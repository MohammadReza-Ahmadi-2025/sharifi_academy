<?php

namespace App\Modules\Home\Support;

use App\Modules\Home\Models\Banner;
use App\Modules\Home\Models\HomeSection;
use App\Modules\Home\Models\HomeSectionTab;
use App\Modules\Home\Services\HomeSectionSyncService;
use Illuminate\Database\Eloquent\Model;

class HomeSectionPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forList(HomeSection $section): array
    {
        $firstTab = $section->tabs->first();

        return [
            'id' => $section->id,
            'title' => $section->title,
            'status' => (bool) $section->status,
            'sort' => $section->sort,
            'content_type' => $firstTab?->content_type,
            'content_type_label' => $firstTab
                ? (AdminHomeEnumOptions::contentTypes()[$firstTab->content_type] ?? $firstTab->content_type)
                : null,
            'tabs_count' => $section->tabs->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forForm(HomeSection $section): array
    {
        return [
            'id' => $section->id,
            'title' => $section->title,
            'status' => (bool) $section->status,
            'sort' => $section->sort,
            'settings' => $section->settings ?? [],
            'tabs' => $section->tabs->map(fn (HomeSectionTab $tab) => self::tabForForm($tab))->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function tabForForm(HomeSectionTab $tab): array
    {
        return [
            'id' => $tab->id,
            'title' => $tab->title,
            'content_type' => $tab->content_type,
            'source_type' => $tab->source_type,
            'limit' => $tab->limit,
            'sort_order' => $tab->sort_order,
            'is_active' => (bool) $tab->is_active,
            'settings' => $tab->settings ?? [],
            'manual_items' => self::manualItemsForTab($tab),
            'manual_item_ids' => self::manualItemIdsForTab($tab),
            'banner_ids' => self::bannerIdsForTab($tab),
        ];
    }

    /**
     * @return list<array{id: int, title: string, slug?: string|null}>
     */
    public static function manualItemsForTab(HomeSectionTab $tab): array
    {
        if ($tab->content_type === 'banner') {
            return [];
        }

        $modelClass = HomeSectionSyncService::modelClass($tab->content_type);

        if (! $modelClass) {
            return [];
        }

        return $tab->items
            ->where('item_type', $modelClass)
            ->sortBy('sort_order')
            ->map(function ($row) {
                $item = $row->item;

                if (! $item instanceof Model) {
                    return null;
                }

                return [
                    'id' => $item->getKey(),
                    'title' => (string) ($item->title ?? $item->name ?? '#'.$item->getKey()),
                    'slug' => $item->slug ?? null,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return list<int>
     */
    public static function manualItemIdsForTab(HomeSectionTab $tab): array
    {
        return array_column(self::manualItemsForTab($tab), 'id');
    }

    /**
     * @return list<int>
     */
    public static function bannerIdsForTab(HomeSectionTab $tab): array
    {
        return $tab->items
            ->where('item_type', Banner::class)
            ->sortBy('sort_order')
            ->pluck('item_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }
}
