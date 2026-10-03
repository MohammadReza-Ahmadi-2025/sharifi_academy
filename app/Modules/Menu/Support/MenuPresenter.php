<?php

namespace App\Modules\Menu\Support;

use App\Modules\Menu\Enums\MenuLocation;
use App\Modules\Menu\Models\Menu;
use App\Modules\Menu\Models\MenuItem;

class MenuPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forList(Menu $menu): array
    {
        return [
            'id' => $menu->id,
            'title' => $menu->title,
            'url' => $menu->url,
            'location' => $menu->location?->value,
            'location_label' => $menu->location instanceof MenuLocation ? $menu->location->label() : $menu->location,
            'sort' => $menu->sort,
            'status' => (bool) $menu->status,
            'items_count' => (int) ($menu->all_items_count ?? 0),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forForm(Menu $menu): array
    {
        return [
            'id' => $menu->id,
            'title' => $menu->title,
            'url' => $menu->url ?? '',
            'icon' => $menu->icon ?? '',
            'location' => $menu->location?->value ?? MenuLocation::Header->value,
            'sort' => $menu->sort,
            'status' => (bool) $menu->status,
            'items' => $menu->allItems->map(fn (MenuItem $item) => self::itemForForm($item))->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function itemForForm(MenuItem $item): array
    {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'type' => $item->type?->value ?? 'external',
            'url' => $item->url ?? '',
            'reference_id' => $item->reference_id,
            'sort' => $item->sort,
            'status' => (bool) $item->status,
            'target_blank' => (bool) $item->target_blank,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forSite(Menu $menu): array
    {
        return [
            'id' => $menu->id,
            'title' => $menu->title,
            'url' => $menu->url ?: null,
            'icon' => $menu->icon,
            'items' => $menu->items->map(fn (MenuItem $item) => [
                'id' => $item->id,
                'title' => $item->title,
                'url' => MenuItemLinkResolver::resolve($item),
                'target_blank' => (bool) $item->target_blank,
            ])->values()->all(),
        ];
    }
}
