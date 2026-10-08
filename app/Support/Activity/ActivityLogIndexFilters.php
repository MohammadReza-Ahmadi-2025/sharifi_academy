<?php

namespace App\Support\Activity;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ActivityLogIndexFilters
{
    /** @var list<int> */
    public const ALLOWED_PER_PAGE = [25, 50, 100];

    /**
     * @return array{
     *     search: string,
     *     module: ?string,
     *     action: ?string,
     *     user_id: ?int,
     *     period: string,
     *     from: ?string,
     *     to: ?string,
     *     per_page: int
     * }
     */
    public function resolve(Request $request): array
    {
        $perPage = (int) $request->input('per_page', 25);

        if (! in_array($perPage, self::ALLOWED_PER_PAGE, true)) {
            $perPage = 25;
        }

        $period = (string) $request->input('period', '');
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

        return [
            'search' => trim((string) $request->input('search', '')),
            'module' => $request->filled('module') ? (string) $request->input('module') : null,
            'action' => $request->filled('action') ? (string) $request->input('action') : null,
            'user_id' => $request->filled('user_id') ? (int) $request->input('user_id') : null,
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'per_page' => $perPage,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  list<string>  $except
     */
    public function apply(Builder $query, array $filters, array $except = []): void
    {
        if (! in_array('search', $except, true) && $filters['search'] !== '') {
            $search = $filters['search'];

            $query->where(function (Builder $q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('subject_type', 'like', "%{$search}%")
                    ->orWhere('route_name', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', function (Builder $userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('family', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");
                    });
            });
        }

        if (! in_array('module', $except, true) && $filters['module']) {
            $query->where('module', $filters['module']);
        }

        if (! in_array('action', $except, true) && $filters['action']) {
            $query->where('action', $filters['action']);
        }

        if (! in_array('user_id', $except, true) && $filters['user_id']) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! in_array('from', $except, true) && $filters['from']) {
            $query->where('created_at', '>=', Carbon::parse($filters['from'])->startOfDay());
        }

        if (! in_array('to', $except, true) && $filters['to']) {
            $query->where('created_at', '<=', Carbon::parse($filters['to'])->endOfDay());
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, int>
     */
    public function actionStats(array $filters): array
    {
        $stats = ActivityLog::query()
            ->tap(fn (Builder $query) => $this->apply($query, $filters, except: ['action']))
            ->selectRaw('action, COUNT(*) as total')
            ->groupBy('action')
            ->pluck('total', 'action');

        return $stats->map(fn ($count) => (int) $count)->all();
    }

    /**
     * @return array<string, string>
     */
    public function moduleOptionsFromDatabase(): array
    {
        $modules = ActivityLog::query()
            ->select('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        $labels = ActivityLogPresenter::moduleLabels();
        $options = [];

        foreach ($modules as $module) {
            $options[$module] = $labels[$module] ?? $module;
        }

        if ($options === []) {
            return $labels;
        }

        return $options;
    }

    /**
     * @return Collection<int, User>
     */
    public function staffUsersForFilter(): Collection
    {
        $actorIds = ActivityLog::query()
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        return User::query()
            ->where(function (Builder $q) use ($actorIds) {
                $q->whereHas('roles');

                if ($actorIds->isNotEmpty()) {
                    $q->orWhereIn('id', $actorIds);
                }
            })
            ->orderBy('name')
            ->orderBy('family')
            ->get(['id', 'name', 'family', 'mobile']);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function hasActiveFilters(array $filters): bool
    {
        return $filters['search'] !== ''
            || $filters['module'] !== null
            || $filters['action'] !== null
            || $filters['user_id'] !== null
            || $filters['period'] !== ''
            || $filters['from'] !== null
            || $filters['to'] !== null;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  iterable<int, User>  $staffUsers
     * @param  array<string, string>  $moduleOptions
     * @return list<array{key: string, label: string}>
     */
    public function activeFilterLabels(
        array $filters,
        iterable $staffUsers,
        array $moduleOptions,
    ): array {
        $labels = [];
        $actionLabels = ActivityLogPresenter::actionLabels();

        if ($filters['search'] !== '') {
            $labels[] = ['key' => 'search', 'label' => 'جستجو: '.$filters['search']];
        }

        if ($filters['user_id']) {
            $user = collect($staffUsers)->firstWhere('id', $filters['user_id']);
            $name = $user ? trim($user->name.' '.$user->family) : '#'.$filters['user_id'];
            $labels[] = ['key' => 'user_id', 'label' => 'کاربر: '.$name];
        }

        if ($filters['action']) {
            $actionLabel = $actionLabels[$filters['action']] ?? $filters['action'];
            $labels[] = ['key' => 'action', 'label' => 'عملیات: '.$actionLabel];
        }

        if ($filters['module']) {
            $labels[] = [
                'key' => 'module',
                'label' => 'بخش: '.($moduleOptions[$filters['module']] ?? $filters['module']),
            ];
        }

        if ($filters['period'] && $filters['period'] !== 'custom') {
            $periodLabel = match ($filters['period']) {
                'today' => 'امروز',
                'week' => '۷ روز اخیر',
                'month' => '۳۰ روز اخیر',
                default => $filters['period'],
            };
            $labels[] = ['key' => 'period', 'label' => 'بازه: '.$periodLabel];
        } elseif ($filters['from'] || $filters['to']) {
            $labels[] = [
                'key' => 'date_range',
                'label' => 'تاریخ: '.($filters['from'] ?? '—').' تا '.($filters['to'] ?? '—'),
            ];
        }

        return $labels;
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
