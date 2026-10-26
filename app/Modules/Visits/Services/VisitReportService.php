<?php

namespace App\Modules\Visits\Services;

use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Courses\Models\Course;
use App\Modules\Visits\Models\Visit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class VisitReportService
{
    /** @var array<string, class-string> */
    public const VISITABLE_TYPES = [
        'product' => Product::class,
        'post' => Post::class,
        'category' => Category::class,
        'brand' => Brand::class,
        'course' => Course::class,
    ];

    /**
     * @return array{
     *     period: string,
     *     from: ?string,
     *     to: ?string,
     *     type: ?string,
     *     per_page: int
     * }
     */
    public function resolveFilters(Request $request): array
    {
        $allowedPerPage = [25, 50, 100];
        $perPage = (int) $request->input('per_page', 25);

        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 25;
        }

        $period = (string) $request->input('period', 'month');
        $from = $request->input('from');
        $to = $request->input('to');

        if ($period !== '' && $period !== 'custom') {
            [$from, $to] = $this->datesForPeriod($period);
        } else {
            $from = $this->normalizeDate($from);
            $to = $this->normalizeDate($to);

            if (($from || $to) && $period === '') {
                $period = 'custom';
            }
        }

        $type = $request->filled('type') ? (string) $request->input('type') : null;

        if ($type && ! array_key_exists($type, self::VISITABLE_TYPES)) {
            $type = null;
        }

        return [
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'type' => $type,
            'per_page' => $perPage,
        ];
    }

    /**
     * @param  array{period: string, from: ?string, to: ?string, type: ?string, per_page: int}  $filters
     */
    public function filteredQuery(array $filters): Builder
    {
        return Visit::query()
            ->when($filters['from'], fn (Builder $q) => $q->whereDate('created_at', '>=', $filters['from']))
            ->when($filters['to'], fn (Builder $q) => $q->whereDate('created_at', '<=', $filters['to']))
            ->when($filters['type'], fn (Builder $q) => $q->where(
                'visitable_type',
                self::VISITABLE_TYPES[$filters['type']],
            ));
    }

    /**
     * @param  array{period: string, from: ?string, to: ?string, type: ?string, per_page: int}  $filters
     * @return array{total: int, logged_in: int, guests: int, unique_visitors: int}
     */
    public function summary(array $filters): array
    {
        $base = $this->filteredQuery($filters);

        $total = (clone $base)->count();
        $loggedIn = (clone $base)->whereNotNull('visitor_id')->count();
        $guests = $total - $loggedIn;

        $uniqueLoggedIn = (clone $base)->whereNotNull('visitor_id')->distinct('visitor_id')->count('visitor_id');
        $uniqueGuests = (clone $base)->whereNull('visitor_id')->distinct('ip_address')->count('ip_address');

        return [
            'total' => $total,
            'logged_in' => $loggedIn,
            'guests' => $guests,
            'unique_visitors' => $uniqueLoggedIn + $uniqueGuests,
        ];
    }

    /**
     * @param  array{period: string, from: ?string, to: ?string, type: ?string, per_page: int}  $filters
     * @return array<string, int>
     */
    public function countsByType(array $filters): array
    {
        $rows = $this->filteredQuery($filters)
            ->selectRaw('visitable_type, COUNT(*) as total')
            ->groupBy('visitable_type')
            ->pluck('total', 'visitable_type');

        $labels = [
            Product::class => 'محصول',
            Post::class => 'مقاله',
            Category::class => 'دسته‌بندی',
            Brand::class => 'برند',
            Course::class => 'دوره',
        ];

        $result = [];

        foreach ($labels as $class => $label) {
            $result[$label] = (int) ($rows[$class] ?? 0);
        }

        return $result;
    }

    /**
     * @param  array{period: string, from: ?string, to: ?string, type: ?string, per_page: int}  $filters
     * @return Collection<int, object{visitable_type: string, visitable_id: int, visits_count: int}>
     */
    public function topVisited(array $filters, int $limit = 25): Collection
    {
        return $this->filteredQuery($filters)
            ->selectRaw('visitable_type, visitable_id, COUNT(*) as visits_count')
            ->groupBy('visitable_type', 'visitable_id')
            ->orderByDesc('visits_count')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  Collection<int, object>  $rows
     * @return array<string, array<int, Model>>
     */
    public function resolveVisitables(Collection $rows): array
    {
        $grouped = $rows->groupBy('visitable_type');
        $resolved = [];

        foreach ($grouped as $type => $items) {
            if (! class_exists($type)) {
                continue;
            }

            $ids = $items->pluck('visitable_id')->unique()->filter()->values();

            $resolved[$type] = $type::query()
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id')
                ->all();
        }

        return $resolved;
    }

    /**
     * @param  array{period: string, from: ?string, to: ?string, type: ?string, per_page: int}  $filters
     * @return Collection<int, object{day: string, total: int}>
     */
    public function dailyTrend(array $filters, int $days = 14): Collection
    {
        $from = $filters['from'] ?? now()->subDays($days - 1)->toDateString();

        return $this->filteredQuery(array_merge($filters, ['from' => $from]))
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get();
    }

    public function adminUrlFor(string $visitableType, int $visitableId, ?object $model): ?string
    {
        if (! $model) {
            return null;
        }

        return match (class_basename($visitableType)) {
            'Product' => route('admin.catalog.products.edit', $model),
            'Post' => route('admin.posts.edit', $model->id),
            'Category' => route('admin.categories.edit', $model).'?type='.urlencode((string) ($model->type ?? 'product')),
            'Brand' => route('admin.catalog.brands.edit', $model),
            'Course' => route('admin.courses.edit', $model),
            default => null,
        };
    }

    public function typeLabelForClass(string $visitableType): string
    {
        return match (class_basename($visitableType)) {
            'Product' => 'محصول',
            'Post' => 'مقاله',
            'Category' => 'دسته‌بندی',
            'Brand' => 'برند',
            'Course' => 'دوره',
            default => class_basename($visitableType) ?: '—',
        };
    }

    public function titleFor(?object $model, string $visitableType, int $visitableId): string
    {
        if (! $model) {
            return class_basename($visitableType)." #{$visitableId} (حذف‌شده)";
        }

        if (isset($model->title)) {
            return (string) $model->title;
        }

        if (isset($model->name)) {
            return (string) $model->name;
        }

        return "#{$visitableId}";
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function datesForPeriod(string $period): array
    {
        return match ($period) {
            'today' => [now()->toDateString(), now()->toDateString()],
            'week' => [now()->subDays(6)->toDateString(), now()->toDateString()],
            'month' => [now()->subDays(29)->toDateString(), now()->toDateString()],
            default => [null, null],
        };
    }

    private function normalizeDate(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
