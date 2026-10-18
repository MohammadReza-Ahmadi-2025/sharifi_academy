<?php

namespace App\Modules\Home\Services;

use App\Models\User;
use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Courses\Models\Course;
use App\Modules\Home\Models\Banner;
use App\Modules\Home\Models\HomeSectionTab;

class HomeSectionSyncService
{
    public static function modelClass(string $contentType): ?string
    {
        return match ($contentType) {
            'product' => Product::class,
            'course' => Course::class,
            'post' => Post::class,
            'brand' => Brand::class,
            'category' => Category::class,
            'instructor' => User::class,
            default => null,
        };
    }

    /**
     * @param  list<int>  $itemIds
     */
    public function syncManualItems(HomeSectionTab $tab, string $contentType, array $itemIds): void
    {
        $modelClass = self::modelClass($contentType);

        if (! $modelClass) {
            return;
        }

        $itemIds = array_values(array_unique(array_filter(array_map('intval', $itemIds))));

        $tab->items()->where('item_type', '!=', $modelClass)->delete();

        $existing = $tab->items()
            ->where('item_type', $modelClass)
            ->get()
            ->keyBy('item_id');

        $keepIds = [];

        foreach ($itemIds as $sortOrder => $itemId) {
            $keepIds[] = $itemId;

            if ($existing->has($itemId)) {
                $existing[$itemId]->update(['sort_order' => $sortOrder]);

                continue;
            }

            $tab->items()->create([
                'item_type' => $modelClass,
                'item_id' => $itemId,
                'sort_order' => $sortOrder,
            ]);
        }

        if ($keepIds === []) {
            $tab->items()->where('item_type', $modelClass)->delete();

            return;
        }

        $tab->items()
            ->where('item_type', $modelClass)
            ->whereNotIn('item_id', $keepIds)
            ->delete();
    }

    /**
     * @param  list<int>  $bannerIds
     */
    public function syncBannerItems(HomeSectionTab $tab, array $bannerIds): void
    {
        $modelClass = Banner::class;
        $bannerIds = array_values(array_unique(array_filter(array_map('intval', $bannerIds))));

        $tab->items()->where('item_type', '!=', $modelClass)->delete();

        $existing = $tab->items()
            ->where('item_type', $modelClass)
            ->get()
            ->keyBy('item_id');

        $keepIds = [];

        foreach ($bannerIds as $sortOrder => $bannerId) {
            $keepIds[] = $bannerId;

            if ($existing->has($bannerId)) {
                $existing[$bannerId]->update(['sort_order' => $sortOrder]);

                continue;
            }

            $tab->items()->create([
                'item_type' => $modelClass,
                'item_id' => $bannerId,
                'sort_order' => $sortOrder,
            ]);
        }

        if ($keepIds === []) {
            $tab->items()->where('item_type', $modelClass)->delete();

            return;
        }

        $tab->items()
            ->where('item_type', $modelClass)
            ->whereNotIn('item_id', $keepIds)
            ->delete();
    }

    public function clearItems(HomeSectionTab $tab): void
    {
        $tab->items()->delete();
    }

    /**
     * @param  array<string, mixed>  $tab
     * @return array<string, mixed>
     */
    public function normalizedTabSettings(array $tab): array
    {
        $settings = is_array($tab['settings'] ?? null) ? $tab['settings'] : [];

        $normalized = array_filter([
            'category_id' => $settings['category_id'] ?? null,
            'brand_id' => $settings['brand_id'] ?? null,
            'title_url' => $settings['title_url'] ?? ($tab['title_url'] ?? null),
            'view_all_url' => $settings['view_all_url'] ?? ($tab['view_all_url'] ?? null),
        ], fn ($value) => $value !== null && $value !== '');

        if (isset($settings['features']) && is_array($settings['features'])) {
            $normalized['features'] = array_values($settings['features']);
        }

        if (isset($settings['testimonials']) && is_array($settings['testimonials'])) {
            $normalized['testimonials'] = array_values(array_map(function ($row) {
                if (! is_array($row)) {
                    return ['name' => '', 'role' => '', 'quote' => ''];
                }

                $rating = (int) ($row['rating'] ?? 5);
                if ($rating < 1 || $rating > 5) {
                    $rating = 5;
                }

                return [
                    'name' => trim((string) ($row['name'] ?? '')),
                    'role' => trim((string) ($row['role'] ?? '')),
                    'quote' => trim((string) ($row['quote'] ?? '')),
                    'rating' => $rating,
                ];
            }, $settings['testimonials']));
        }

        return $normalized;
    }
}
