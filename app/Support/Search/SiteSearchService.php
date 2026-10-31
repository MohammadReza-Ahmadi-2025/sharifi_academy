<?php

namespace App\Support\Search;

use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Courses\Models\Course;

class SiteSearchService
{
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
        $items = [];

        if (($limits['course'] ?? 0) > 0) {
            foreach (Course::search($q)->take($limits['course'])->get() as $course) {
                $items[] = $this->courseItem($course);
            }
        }

        if (($limits['book'] ?? 0) > 0) {
            foreach (Product::search($q)->take($limits['book'])->get() as $product) {
                $items[] = $this->productItem($product);
            }
        }

        if (($limits['post'] ?? 0) > 0) {
            foreach (Post::search($q)->take($limits['post'])->get() as $post) {
                $items[] = $this->postItem($post);
            }
        }

        if (($limits['category'] ?? 0) > 0) {
            foreach (Category::search($q)->take($limits['category'])->get() as $category) {
                $items[] = $this->categoryItem($category);
            }
        }

        if (($limits['brand'] ?? 0) > 0) {
            foreach (Brand::search($q)->take($limits['brand'])->get() as $brand) {
                $items[] = $this->brandItem($brand);
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
        $items = [];

        if (($limits['course'] ?? 0) > 0) {
            foreach (Course::query()->published()->where('title', 'like', "%{$q}%")->limit($limits['course'])->get() as $course) {
                $items[] = $this->courseItem($course);
            }
        }

        if (($limits['book'] ?? 0) > 0) {
            foreach (Product::query()->active()->visible()->where('title', 'like', "%{$q}%")->limit($limits['book'])->get() as $product) {
                $items[] = $this->productItem($product);
            }
        }

        if (($limits['post'] ?? 0) > 0) {
            foreach (Post::query()->published()->where('title', 'like', "%{$q}%")->limit($limits['post'])->get() as $post) {
                $items[] = $this->postItem($post);
            }
        }

        if (($limits['category'] ?? 0) > 0) {
            foreach (
                Category::query()
                    ->active()
                    ->whereIn('type', [Category::TYPE_PRODUCT, Category::TYPE_COURSE, Category::TYPE_BLOG])
                    ->where('title', 'like', "%{$q}%")
                    ->limit($limits['category'])
                    ->get() as $category
            ) {
                $items[] = $this->categoryItem($category);
            }
        }

        if (($limits['brand'] ?? 0) > 0) {
            foreach (Brand::query()->where('status', true)->where('title', 'like', "%{$q}%")->limit($limits['brand'])->get() as $brand) {
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
