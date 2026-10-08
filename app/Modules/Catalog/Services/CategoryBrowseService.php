<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Support\AdminCategoryTypes;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Services\CourseCategoryBrowseService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class CategoryBrowseService
{
    public function __construct(
        private readonly CourseCategoryBrowseService $courseBrowse,
        private readonly ProductCategoryBrowseService $productBrowse,
    ) {}

    /**
     * @return array{
     *     listing: string,
     *     items_label: string,
     *     items: LengthAwarePaginator<int, array<string, mixed>>,
     *     filters: array<string, mixed>,
     *     filterOptions: array<string, mixed>,
     *     subcategories: list<array{id: int, title: string, slug: string, items_count: int}>
     * }
     */
    public function forPublicCategory(Category $category, Request $request): array
    {
        abort_unless($category->status, 404);

        $listing = match ($category->type) {
            Category::TYPE_COURSE => 'courses',
            Category::TYPE_PRODUCT => 'products',
            default => null,
        };

        abort_if($listing === null, 404);

        if ((int) $request->query('page', 1) <= 1) {
            $category->recordVisit();
        }

        if ($listing === 'courses') {
            return $this->courses($category, $request);
        }

        return $this->products($category, $request);
    }

    /**
     * @return array{
     *     listing: string,
     *     items_label: string,
     *     items: LengthAwarePaginator<int, array<string, mixed>>,
     *     filters: array<string, mixed>,
     *     filterOptions: array<string, mixed>,
     *     subcategories: list<array{id: int, title: string, slug: string, items_count: int}>
     * }
     */
    private function courses(Category $category, Request $request): array
    {
        $this->loadChildrenWithCounts($category, Category::TYPE_COURSE, 'courses');

        $filters = $this->courseBrowse->filtersFromRequest($request);
        $items = $this->courseBrowse->paginateCoursesInCategory($category, $filters);

        return [
            'listing' => 'courses',
            'items_label' => AdminCategoryTypes::label(Category::TYPE_COURSE),
            'items' => $items,
            'filters' => $filters,
            'filterOptions' => [
                'levels' => $this->courseBrowse->levelOptions(),
                'sorts' => $this->courseBrowse->sortOptions(),
                'brands' => [],
            ],
            'subcategories' => $this->mapSubcategories($category),
        ];
    }

    /**
     * @return array{
     *     listing: string,
     *     items_label: string,
     *     items: LengthAwarePaginator<int, array<string, mixed>>,
     *     filters: array<string, mixed>,
     *     filterOptions: array<string, mixed>,
     *     subcategories: list<array{id: int, title: string, slug: string, items_count: int}>
     * }
     */
    private function products(Category $category, Request $request): array
    {
        $this->loadChildrenWithCounts($category, Category::TYPE_PRODUCT, 'products');

        $filters = $this->productBrowse->filtersFromRequest($request);
        $items = $this->productBrowse->paginateProductsInCategory($category, $filters);

        $brands = $this->productBrowse->brandsInCategory($category)
            ->map(fn ($brand) => [
                'value' => $brand->slug,
                'label' => $brand->title,
            ])
            ->values()
            ->all();

        return [
            'listing' => 'products',
            'items_label' => AdminCategoryTypes::label(Category::TYPE_PRODUCT),
            'items' => $items,
            'filters' => $filters,
            'filterOptions' => [
                'levels' => [],
                'sorts' => $this->productBrowse->sortOptions(),
                'brands' => $brands,
            ],
            'subcategories' => $this->mapSubcategories($category),
        ];
    }

    private function loadChildrenWithCounts(Category $category, string $type, string $relation): void
    {
        $category->load([
            'image',
            'activeChildren' => function ($query) use ($type, $relation) {
                $query->where('type', $type);

                if ($relation === 'courses') {
                    $query->withCount([
                        'courses as items_count' => fn ($cq) => $cq
                            ->where('status', CourseStatus::Published)
                            ->whereNull('courses.deleted_at'),
                    ]);
                } else {
                    $query->withCount([
                        'products as items_count' => fn ($pq) => $pq
                            ->where('status', true)
                            ->where('is_visible', true)
                            ->whereNull('products.deleted_at'),
                    ]);
                }
            },
        ]);
    }

    /**
     * @return array{items: LengthAwarePaginator<int, array<string, mixed>>}
     */
    public function paginateItemsOnly(Category $category, Request $request): array
    {
        abort_unless($category->status, 404);

        $listing = match ($category->type) {
            Category::TYPE_COURSE => 'courses',
            Category::TYPE_PRODUCT => 'products',
            default => null,
        };

        abort_if($listing === null, 404);

        if ($listing === 'courses') {
            $filters = $this->courseBrowse->filtersFromRequest($request);

            return [
                'items' => $this->courseBrowse->paginateCoursesInCategory($category, $filters),
            ];
        }

        $filters = $this->productBrowse->filtersFromRequest($request);

        return [
            'items' => $this->productBrowse->paginateProductsInCategory($category, $filters),
        ];
    }

    /**
     * @return list<array{id: int, title: string, slug: string, items_count: int}>
     */
    private function mapSubcategories(Category $category): array
    {
        return $category->activeChildren
            ->filter(fn (Category $child) => (int) $child->items_count > 0)
            ->map(fn (Category $child) => [
                'id' => $child->id,
                'title' => $child->title,
                'slug' => $child->slug,
                'items_count' => (int) $child->items_count,
            ])
            ->values()
            ->all();
    }
}
