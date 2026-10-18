<?php

namespace App\Modules\Menu\Support;

use App\Modules\Catalog\Models\Category;
use App\Modules\Menu\Enums\MenuItemType;
use App\Modules\Menu\Models\MenuItem;
use App\Modules\Pages\Models\Page;

class MenuItemLinkResolver
{
    public static function resolve(MenuItem $item): string
    {
        $type = $item->type instanceof MenuItemType ? $item->type : MenuItemType::tryFrom((string) $item->type);

        return match ($type) {
            MenuItemType::CategoryProduct => self::categoryLink($item, '/categories/'),
            MenuItemType::CategoryCourse => self::categoryLink($item, '/categories/'),
            MenuItemType::Page => self::pageLink($item),
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

    private static function pageLink(MenuItem $item): string
    {
        if ($item->reference_id) {
            $page = Page::query()->active()->find($item->reference_id);

            if ($page !== null) {
                return route('pages.show', ['page' => $page->publicSlug()]);
            }
        }

        return $item->url ?: '#';
    }
}
