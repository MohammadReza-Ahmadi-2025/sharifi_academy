<?php

namespace App\Modules\Courses\Services;

use App\Modules\Catalog\Models\Category;
use App\Modules\Courses\Enums\CourseLevel;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Support\CourseCatalogPresenter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;

class CourseCategoryBrowseService
{
    public const SORT_NEWEST = 'newest';

    public const SORT_TITLE = 'title';

    public const SORT_PRICE_ASC = 'price_asc';

    public const SORT_PRICE_DESC = 'price_desc';

    /**
     * @return array<string, mixed>
     */
    public function filtersFromRequest(Request $request): array
    {
        $sort = (string) $request->query('sort', self::SORT_NEWEST);
        if (! in_array($sort, [self::SORT_NEWEST, self::SORT_TITLE, self::SORT_PRICE_ASC, self::SORT_PRICE_DESC], true)) {
            $sort = self::SORT_NEWEST;
        }

        $level = trim((string) $request->query('level', ''));
        if ($level !== '' && ! in_array($level, CourseLevel::values(), true)) {
            $level = '';
        }

        $featured = $request->query('featured');
        $featuredOnly = $featured === '1' || $featured === 'true';

        return [
            'q' => trim((string) $request->query('q', '')),
            'level' => $level,
            'featured' => $featuredOnly,
            'sort' => $sort,
            'per_page' => $this->perPage($request),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateCoursesInCategory(Category $category, array $filters): LengthAwarePaginator
    {
        $query = $category->courses()
            ->where('courses.status', CourseStatus::Published)
            ->whereNull('courses.deleted_at')
            ->with(['mainImage'])
            ->withMin(
                ['courseClasses as min_public_price' => fn (Builder $classes) => $classes->where('is_public', true)],
                'price',
            );

        $this->applyFilters($query, $filters);
        $this->applySort($query, (string) $filters['sort']);

        return $query
            ->paginate((int) $filters['per_page'])
            ->withQueryString()
            ->through(fn (Course $course) => CourseCatalogPresenter::forCard($course));
    }

    /**
     * @param  Builder<Course>|BelongsToMany<Course, Category>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder|BelongsToMany $query, array $filters): void
    {
        $search = (string) ($filters['q'] ?? '');
        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->where('courses.title', 'like', '%'.$search.'%')
                    ->orWhere('courses.short_description', 'like', '%'.$search.'%')
                    ->orWhere('courses.code', 'like', '%'.$search.'%');
            });
        }

        $level = (string) ($filters['level'] ?? '');
        if ($level !== '') {
            $query->where('courses.level', $level);
        }

        if (($filters['featured'] ?? false) === true) {
            $query->where('courses.is_featured', true);
        }
    }

    /**
     * @param  Builder<Course>|BelongsToMany<Course, Category>  $query
     */
    private function applySort(Builder|BelongsToMany $query, string $sort): void
    {
        if ($sort === self::SORT_TITLE) {
            $query->orderBy('courses.title');

            return;
        }

        if ($sort === self::SORT_PRICE_ASC || $sort === self::SORT_PRICE_DESC) {
            $query->withMin(
                ['courseClasses as min_public_price' => fn (Builder $classes) => $classes->where('is_public', true)],
                'price',
            );
            $query->orderBy(
                'min_public_price',
                $sort === self::SORT_PRICE_ASC ? 'asc' : 'desc',
            );

            return;
        }

        $query->orderBy('courses.display_order')->orderByDesc('courses.published_at');
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function levelOptions(): array
    {
        return array_map(
            fn (CourseLevel $level) => ['value' => $level->value, 'label' => $level->label()],
            CourseLevel::cases(),
        );
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function sortOptions(): array
    {
        return [
            ['value' => self::SORT_NEWEST, 'label' => 'جدیدترین'],
            ['value' => self::SORT_TITLE, 'label' => 'عنوان (الفبا)'],
            ['value' => self::SORT_PRICE_ASC, 'label' => 'ارزان‌ترین'],
            ['value' => self::SORT_PRICE_DESC, 'label' => 'گران‌ترین'],
        ];
    }

    private function perPage(Request $request): int
    {
        return 24;
    }
}
