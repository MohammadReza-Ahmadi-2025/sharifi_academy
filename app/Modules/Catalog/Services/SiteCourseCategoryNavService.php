<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SiteCourseCategoryNavService
{
    public const CACHE_KEY = 'site:course-category-nav';

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function tree(): array
    {
        $cached = Cache::get(self::CACHE_KEY);
        if (is_array($cached)) {
            if ($cached !== [] || ! $this->hasActiveCourseCategories()) {
                return $cached;
            }

            Cache::forget(self::CACHE_KEY);
        }

        return Cache::remember(self::CACHE_KEY, 600, function () {
            $categories = Category::query()
                ->course()
                ->active()
                ->orderBy('sort')
                ->orderBy('title')
                ->get(['id', 'parent_id', 'title', 'slug']);

            return $this->buildTree($categories);
        });
    }

    private function hasActiveCourseCategories(): bool
    {
        return Category::query()->course()->active()->exists();
    }

    /**
     * @param  Collection<int, Category>  $categories
     * @return list<array<string, mixed>>
     */
    private function buildTree(Collection $categories, ?int $parentId = null): array
    {
        return $categories
            ->filter(fn (Category $category) => $this->matchesParent($category, $parentId))
            ->map(function (Category $category) use ($categories) {
                $slug = $category->publicSlug();

                return [
                    'id' => $category->id,
                    'title' => $category->title,
                    'slug' => $slug,
                    'url' => '/categories/'.$slug,
                    'children' => $this->buildTree($categories, $category->id),
                ];
            })
            ->values()
            ->all();
    }

    private function matchesParent(Category $category, ?int $parentId): bool
    {
        if ($parentId === null) {
            return $category->parent_id === null || (int) $category->parent_id === 0;
        }

        return (int) $category->parent_id === $parentId;
    }
}
