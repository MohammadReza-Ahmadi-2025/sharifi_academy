<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Courses\Models\Course;
use App\Support\Search\SiteSearchUrls;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiteSearchController extends Controller
{
    public function autocomplete(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $q = preg_replace('/[^\p{L}\p{N}\s\-]/u', '', $q) ?? '';

        if (mb_strlen($q) < 2) {
            return response()->json(['items' => []]);
        }

        if (config('scout.driver') === 'meilisearch') {
            try {
                return response()->json([
                    'items' => $this->itemsFromScout($q),
                ]);
            } catch (\Throwable) {
                // fall through to database search
            }
        }

        return response()->json([
            'items' => $this->itemsFromDatabase($q),
        ]);
    }

    /**
     * @return list<array{id: string, kind: string, title: string, subtitle: string, url: string}>
     */
    private function itemsFromScout(string $q): array
    {
        $items = [];

        foreach (Course::search($q)->take(4)->get() as $course) {
            $items[] = $this->courseItem($course);
        }

        foreach (Product::search($q)->take(4)->get() as $product) {
            $items[] = $this->productItem($product);
        }

        foreach (Post::search($q)->take(3)->get() as $post) {
            $items[] = $this->postItem($post);
        }

        foreach (Category::search($q)->take(3)->get() as $category) {
            $items[] = $this->categoryItem($category);
        }

        foreach (Brand::search($q)->take(3)->get() as $brand) {
            $items[] = $this->brandItem($brand);
        }

        return $items;
    }

    /**
     * @return list<array{id: string, kind: string, title: string, subtitle: string, url: string}>
     */
    private function itemsFromDatabase(string $q): array
    {
        $items = [];

        foreach (Course::query()->published()->where('title', 'like', "%{$q}%")->limit(4)->get() as $course) {
            $items[] = $this->courseItem($course);
        }

        foreach (Product::query()->active()->visible()->where('title', 'like', "%{$q}%")->limit(4)->get() as $product) {
            $items[] = $this->productItem($product);
        }

        foreach (Post::query()->published()->where('title', 'like', "%{$q}%")->limit(3)->get() as $post) {
            $items[] = $this->postItem($post);
        }

        foreach (
            Category::query()
                ->active()
                ->whereIn('type', [Category::TYPE_PRODUCT, Category::TYPE_COURSE, Category::TYPE_BLOG])
                ->where('title', 'like', "%{$q}%")
                ->limit(3)
                ->get() as $category
        ) {
            $items[] = $this->categoryItem($category);
        }

        foreach (Brand::query()->where('status', true)->where('title', 'like', "%{$q}%")->limit(3)->get() as $brand) {
            $items[] = $this->brandItem($brand);
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
