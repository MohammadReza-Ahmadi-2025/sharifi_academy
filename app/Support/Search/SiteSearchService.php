<?php

namespace App\Support\Search;

use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Courses\Models\Course;

class SiteSearchService
{
    public function __construct(private readonly PersianSearchQuery $persianQuery = new PersianSearchQuery) {}

    /**
     * @return list<array{id: string, kind: string, title: string, subtitle: string, url: string}>
     */
    public function autocomplete(string $q): array
    {
        $q = $this->sanitize($q);
        if (mb_strlen($q) < 2) {
            return [];
        }

        if (config('scout.driver') === 'meilisearch') {
            try {
                return $this->itemsFromScout($q, [
                    'course' => 4,
                    'book' => 4,
                    'post' => 3,
                    'category' => 3,
                    'brand' => 3,
                ]);
            } catch (\Throwable) {
                // fall through
            }
        }

        return $this->itemsFromDatabase($q, [
            'course' => 4,
            'book' => 4,
            'post' => 3,
            'category' => 3,
            'brand' => 3,
        ]);
    }

    /**
     * @return array{
     *     q: string,
     *     total: int,
     *     groups: array<string, list<array{id: string, kind: string, title: string, subtitle: string, url: string}>>
     * }
     */
    public function results(string $q, ?string $kind = null): array
    {
        $q = $this->sanitize($q);
        if (mb_strlen($q) < 2) {
            return [
                'q' => $q,
                'total' => 0,
                'groups' => [],
            ];
        }

        $limits = [
            'course' => 24,
            'book' => 24,
            'post' => 24,
            'category' => 12,
            'brand' => 12,
        ];

        if ($kind !== null && isset($limits[$kind])) {
            $limits = [$kind => $limits[$kind]];
        }

        $items = [];
        if (config('scout.driver') === 'meilisearch') {
            try {
                $items = $this->itemsFromScout($q, $limits);
            } catch (\Throwable) {
                $items = $this->itemsFromDatabase($q, $limits);
            }
        } else {
            $items = $this->itemsFromDatabase($q, $limits);
        }

        $groups = [];
        foreach ($items as $item) {
            $groups[$item['kind']][] = $item;
        }

        return [
            'q' => $q,
            'total' => count($items),
            'groups' => $groups,
        ];
    }

    public function sanitize(string $q): string
    {
        $q = trim($q);
        $q = preg_replace('/[^\p{L}\p{N}\s\-]/u', '', $q) ?? '';

        return preg_replace('/\s+/u', ' ', $q) ?? $q;
    }

    /**
     * @param  array<string, int>  $limits
     * @return list<array{id: string, kind: string, title: string, subtitle: string, url: string}>
     */
    private function itemsFromScout(string $q, array $limits): array
    {
        $queries = $this->persianQuery->variants($q);
        $items = [];

        if (($limits['course'] ?? 0) > 0) {
            $items = [...$items, ...$this->scoutMap(Course::class, $queries, $limits['course'], fn (Course $m) => $this->courseItem($m))];
        }

        if (($limits['book'] ?? 0) > 0) {
            $items = [...$items, ...$this->scoutMap(Product::class, $queries, $limits['book'], fn (Product $m) => $this->productItem($m))];
        }

        if (($limits['post'] ?? 0) > 0) {
            $items = [...$items, ...$this->scoutMap(Post::class, $queries, $limits['post'], fn (Post $m) => $this->postItem($m))];
        }

        if (($limits['category'] ?? 0) > 0) {
            $items = [...$items, ...$this->scoutMap(Category::class, $queries, $limits['category'], fn (Category $m) => $this->categoryItem($m))];
        }

        if (($limits['brand'] ?? 0) > 0) {
            $items = [...$items, ...$this->scoutMap(Brand::class, $queries, $limits['brand'], fn (Brand $m) => $this->brandItem($m))];
        }

        return $items;
    }

    /**
     * @param  class-string  $modelClass
     * @param  list<string>  $queries
     * @param  callable(object): array{id: string, kind: string, title: string, subtitle: string, url: string}  $mapper
     * @return list<array{id: string, kind: string, title: string, subtitle: string, url: string}>
     */
    private function scoutMap(string $modelClass, array $queries, int $limit, callable $mapper): array
    {
        $seen = [];
        $items = [];

        foreach ($queries as $query) {
            foreach ($modelClass::search($query)->take($limit)->get() as $model) {
                $item = $mapper($model);
                if (isset($seen[$item['id']])) {
                    continue;
                }
                $seen[$item['id']] = true;
                $items[] = $item;
                if (count($items) >= $limit) {
                    return $items;
                }
            }
        }

        return $items;
    }

    /**
     * @param  array<string, int>  $limits
     * @return list<array{id: string, kind: string, title: string, subtitle: string, url: string}>
     */
    private function itemsFromDatabase(string $q, array $limits): array
    {
        $queries = $this->persianQuery->variants($q);
        $items = [];

        if (($limits['course'] ?? 0) > 0) {
            foreach (
                Course::query()->published()->where(function ($builder) use ($queries): void {
                    foreach ($queries as $query) {
                        $builder->orWhere('title', 'like', "%{$query}%");
                    }
                })->limit($limits['course'])->get() as $course
            ) {
                $items[] = $this->courseItem($course);
            }
        }

        if (($limits['book'] ?? 0) > 0) {
            foreach (
                Product::query()->active()->visible()->where(function ($builder) use ($queries): void {
                    foreach ($queries as $query) {
                        $builder->orWhere('title', 'like', "%{$query}%");
                    }
                })->limit($limits['book'])->get() as $product
            ) {
                $items[] = $this->productItem($product);
            }
        }

        if (($limits['post'] ?? 0) > 0) {
            foreach (
                Post::query()->published()->where(function ($builder) use ($queries): void {
                    foreach ($queries as $query) {
                        $builder->orWhere('title', 'like', "%{$query}%");
                    }
                })->limit($limits['post'])->get() as $post
            ) {
                $items[] = $this->postItem($post);
            }
        }

        if (($limits['category'] ?? 0) > 0) {
            foreach (
                Category::query()
                    ->active()
                    ->whereIn('type', [Category::TYPE_PRODUCT, Category::TYPE_COURSE, Category::TYPE_BLOG])
                    ->where(function ($builder) use ($queries): void {
                        foreach ($queries as $query) {
                            $builder->orWhere('title', 'like', "%{$query}%");
                        }
                    })
                    ->limit($limits['category'])
                    ->get() as $category
            ) {
                $items[] = $this->categoryItem($category);
            }
        }

        if (($limits['brand'] ?? 0) > 0) {
            foreach (
                Brand::query()->where('status', true)->where(function ($builder) use ($queries): void {
                    foreach ($queries as $query) {
                        $builder->orWhere('title', 'like', "%{$query}%");
                    }
                })->limit($limits['brand'])->get() as $brand
            ) {
                $items[] = $this->brandItem($brand);
            }
        }

        return $items;
    }

    /**
     * @return array{id: string, kind: string, title: string, subtitle: string, url: string}
     */
    private function courseItem(Course $course): array
    {
        return [
            'id' => 'course-'.$course->id,
            'kind' => 'course',
            'title' => (string) $course->title,
            'subtitle' => (string) ($course->short_description ?: $course->code),
            'url' => SiteSearchUrls::course($course),
        ];
    }

    /**
     * @return array{id: string, kind: string, title: string, subtitle: string, url: string}
     */
    private function productItem(Product $product): array
    {
        $product->loadMissing('brand');

        return [
            'id' => 'book-'.$product->id,
            'kind' => 'book',
            'title' => (string) $product->title,
            'subtitle' => (string) ($product->brand?->title ?? $product->short_description ?? ''),
            'url' => SiteSearchUrls::product($product),
        ];
    }

    /**
     * @return array{id: string, kind: string, title: string, subtitle: string, url: string}
     */
    private function postItem(Post $post): array
    {
        return [
            'id' => 'post-'.$post->id,
            'kind' => 'post',
            'title' => (string) $post->title,
            'subtitle' => (string) ($post->excerpt ?? ''),
            'url' => SiteSearchUrls::post($post),
        ];
    }

    /**
     * @return array{id: string, kind: string, title: string, subtitle: string, url: string}
     */
    private function categoryItem(Category $category): array
    {
        $kindLabel = match ($category->type) {
            Category::TYPE_COURSE => 'دسته دوره',
            Category::TYPE_BLOG => 'دسته وبلاگ',
            default => 'دسته',
        };

        return [
            'id' => 'category-'.$category->id,
            'kind' => 'category',
            'title' => (string) $category->title,
            'subtitle' => $kindLabel,
            'url' => SiteSearchUrls::category($category),
        ];
    }

    /**
     * @return array{id: string, kind: string, title: string, subtitle: string, url: string}
     */
    private function brandItem(Brand $brand): array
    {
        return [
            'id' => 'brand-'.$brand->id,
            'kind' => 'brand',
            'title' => (string) $brand->title,
            'subtitle' => (string) ($brand->description ?? ''),
            'url' => SiteSearchUrls::brand($brand),
        ];
    }
}
