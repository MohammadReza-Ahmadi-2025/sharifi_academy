<?php

namespace App\Modules\Menu\Support;

use App\Modules\Catalog\Models\Category;
use App\Modules\Menu\Enums\MenuItemType;
use App\Modules\Menu\Models\MenuItem;

class MenuItemLinkResolver
{
    public static function resolve(MenuItem $item): string
    {
        $type = $item->type instanceof MenuItemType ? $item->type : MenuItemType::tryFrom((string) $item->type);

        return match ($type) {
            MenuItemType::CategoryProduct => self::categoryLink($item, '/categories/'),
            MenuItemType::CategoryCourse => self::categoryLink($item, '/categories/'),
            MenuItemType::Internal => $item->url ?: '#',
            MenuItemType::External => $item->url ?: '#',
            default => $item->url ?: '#',
        };
    }

    private static function categoryLink(MenuItem $item, string $prefix): string
    {
        if ($item->reference_id) {
            $slug = Category::query()->whereKey($item->reference_id)->value('slug');

            if ($slug) {
                return $prefix.$slug;
            }
        }

        return $item->url ?: '#';
    }
}
