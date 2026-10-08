<?php

namespace App\Support;

use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SiteMenuCategoryService
{
    /**
     * @return Collection<int, Category>
     */
    public function productRoots(): Collection
    {
        return $this->rememberCategoryRoots(Category::TYPE_PRODUCT);
    }

    /**
     * @return Collection<int, Category>
     */
    public function courseRoots(): Collection
    {
        return $this->rememberCategoryRoots(Category::TYPE_COURSE);
    }

    public static function flush(): void
    {
        Cache::forget('site:menu:product_categories');
        Cache::forget('site:menu:course_categories');
    }

    /**
     * کش فقط شناسه‌هاست؛ مدل‌ها هر بار از دیتابیس لود می‌شوند (جلوگیری از خطای unserialize).
     *
     * @return Collection<int, Category>
     */
    private function rememberCategoryRoots(string $type): Collection
    {
        $cacheKey = $type === Category::TYPE_COURSE
            ? 'site:menu:course_categories'
            : 'site:menu:product_categories';

        $ids = Cache::remember($cacheKey, 600, function () use ($type) {
            return $this->loadRoots($type)->pluck('id')->all();
        });

        if ($ids === []) {
            return collect();
        }

        return Category::query()
            ->whereIn('id', $ids)
            ->where('type', $type)
            ->whereNull('parent_id')
            ->active()
            ->where('show_in_menu', true)
            ->with([
                'image',
                'childrenRecursive' => function ($query) use ($type) {
                    $query->select('id', 'parent_id', 'title', 'slug', 'sort', 'type', 'status', 'description')
                        ->where('type', $type)
                        ->where('status', 1)
                        ->orderBy('sort');
                },
            ])
            ->orderBy('sort')
            ->get()
            ->sortBy(fn (Category $category) => array_search($category->id, $ids, true))
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    public function megaForCourseCategories(): array
    {
        return $this->buildMega(
            roots: $this->courseRoots(),
            viewAllLabel: 'مشاهده همه دوره‌ها',
            viewAllUrl: '/#courses',
            linkPrefix: '/categories/',
            featuredFallbackTitle: 'دوره‌های آکادمی',
            featuredFallbackDescription: 'کلاس‌های تخصصی با اساتید مجرب — ثبت‌نام آنلاین.',
            featuredUrl: '/#courses',
            featuredImage: '/coursetest.png',
            brands: collect(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function megaForProductCategories(): array
    {
        $roots = $this->productRoots();

        return $this->buildMega(
            roots: $roots,
            viewAllLabel: 'مشاهده همه کتاب‌ها',
            viewAllUrl: '/#books',
            linkPrefix: '/books?category=',
            featuredFallbackTitle: 'کتاب‌های تخصصی آکادمی',
            featuredFallbackDescription: 'منابع PDF برای همراهی با دوره‌های آموزشی.',
            featuredUrl: '/#books',
            featuredImage: '/banner.png',
            brands: $this->brandsForMenu($roots),
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function productRootsForHeader(): array
    {
        return $this->productRoots()
            ->map(fn (Category $category) => $this->mapRootForHeader($category))
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, Category>
     */
    private function loadRoots(string $type): Collection
    {
        return Category::query()
            ->where('type', $type)
            ->whereNull('parent_id')
            ->active()
            ->where('show_in_menu', true)
            ->with([
                'image',
                'childrenRecursive' => function ($query) use ($type) {
                    $query->select('id', 'parent_id', 'title', 'slug', 'sort', 'type', 'status')
                        ->where('type', $type)
                        ->where('status', 1)
                        ->orderBy('sort');
                },
            ])
            ->orderBy('sort')
            ->get();
    }

    /**
     * @param  Collection<int, Category>  $roots
     * @param  Collection<int, Brand>  $brands
     * @return array<string, mixed>
     */
    private function buildMega(
        Collection $roots,
        string $viewAllLabel,
        string $viewAllUrl,
        string $linkPrefix,
        string $featuredFallbackTitle,
        string $featuredFallbackDescription,
        string $featuredUrl,
        string $featuredImage,
        Collection $brands,
    ): array {
        $columns = [];

        foreach ($roots as $root) {
            $links = $this->linksForCategoryTree($root, $linkPrefix);

            if ($links === []) {
                $links[] = [
                    'label' => $root->title,
                    'url' => $linkPrefix.$root->slug,
                    'description' => $root->description,
                ];
            }

            $columns[] = [
                'title' => $root->title,
                'links' => $links,
            ];
        }

        if ($brands->isNotEmpty()) {
            $columns[] = [
                'title' => 'برندها',
                'links' => $brands->map(fn (Brand $brand) => [
                    'label' => $brand->title,
                    'url' => '/books?brand='.$brand->slug,
                    'description' => null,
                ])->all(),
            ];
        }

        if ($columns === []) {
            $columns[] = [
                'title' => 'مرور',
                'links' => [
                    [
                        'label' => $viewAllLabel,
                        'url' => $viewAllUrl,
                        'description' => null,
                    ],
                ],
            ];
        }

        $featuredRoot = $roots->first(fn (Category $category) => $category->image !== null) ?? $roots->first();

        return [
            'view_all' => [
                'label' => $viewAllLabel,
                'url' => $viewAllUrl,
            ],
            'featured' => [
                'title' => $featuredRoot?->title ?? $featuredFallbackTitle,
                'description' => $featuredRoot?->description ?? $featuredFallbackDescription,
                'url' => $featuredRoot ? $linkPrefix.$featuredRoot->slug : $featuredUrl,
                'image' => $featuredRoot?->image?->publicUrl() ?? $featuredImage,
            ],
            'columns' => $columns,
        ];
    }

    /**
     * @return list<array{label: string, url: string, description: string|null}>
     */
    private function linksForCategoryTree(Category $root, string $linkPrefix): array
    {
        $links = [];
        $children = $root->childrenRecursive ?? collect();

        foreach ($children as $child) {
            $links[] = [
                'label' => $child->title,
                'url' => $linkPrefix.$child->slug,
                'description' => $child->description,
            ];

            foreach ($child->childrenRecursive ?? [] as $grandchild) {
                $links[] = [
                    'label' => $grandchild->title,
                    'url' => $linkPrefix.$grandchild->slug,
                    'description' => $grandchild->description,
                ];
            }
        }

        return $links;
    }

    /**
     * @param  Collection<int, Category>  $roots
     * @return Collection<int, Brand>
     */
    private function brandsForMenu(Collection $roots): Collection
    {
        if ($roots->isEmpty()) {
            return collect();
        }

        $categoryIds = [];
        foreach ($roots as $root) {
            $categoryIds = array_merge($categoryIds, $this->collectCategoryTreeIds($root));
        }
        $categoryIds = array_values(array_unique($categoryIds));

        if ($categoryIds === []) {
            return collect();
        }

        return Brand::query()
            ->select('id', 'title', 'slug', 'sort')
            ->where('status', true)
            ->whereHas('products', function ($query) use ($categoryIds) {
                $query->active()
                    ->visible()
                    ->whereHas('categories', function ($categoryQuery) use ($categoryIds) {
                        $categoryQuery->whereIn('categories.id', $categoryIds);
                    });
            })
            ->orderBy('sort')
            ->orderBy('title')
            ->limit(8)
            ->get();
    }

    /**
     * @return list<int>
     */
    private function collectCategoryTreeIds(Category $category): array
    {
        $ids = [$category->id];

        foreach ($category->childrenRecursive ?? [] as $child) {
            $ids = array_merge($ids, $this->collectCategoryTreeIds($child));
        }

        return $ids;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapRootForHeader(Category $category): array
    {
        $children = collect($category->childrenRecursive ?? [])
            ->map(fn (Category $child) => [
                'id' => $child->id,
                'title' => $child->title,
                'slug' => $child->slug,
                'url' => '/categories/'.$child->slug,
                'children' => collect($child->childrenRecursive ?? [])
                    ->map(fn (Category $grandchild) => [
                        'id' => $grandchild->id,
                        'title' => $grandchild->title,
                        'slug' => $grandchild->slug,
                        'url' => '/categories/'.$grandchild->slug,
                    ])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();

        return [
            'id' => $category->id,
            'title' => $category->title,
            'slug' => $category->slug,
            'url' => '/categories/'.$category->slug,
            'image_url' => $category->image?->publicUrl(),
            'children' => $children,
            'brands' => $this->brandsForMenu(collect([$category]))
                ->map(fn (Brand $brand) => [
                    'id' => $brand->id,
                    'title' => $brand->title,
                    'slug' => $brand->slug,
                    'url' => '/books?brand='.$brand->slug,
                ])
                ->values()
                ->all(),
        ];
    }
}
