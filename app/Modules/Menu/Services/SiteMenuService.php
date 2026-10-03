<?php

namespace App\Modules\Menu\Services;

use App\Modules\Menu\Models\Menu;
use App\Modules\Menu\Support\MenuItemLinkResolver;
use App\Modules\Menu\Support\MenuPresenter;
use Illuminate\Support\Facades\Cache;

class SiteMenuService
{
    public static function flush(): void
    {
        Cache::forget('site:menus');
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public function groupedByLocation(): array
    {
        return Cache::remember('site:menus', 600, function () {
            $menus = Menu::query()
                ->where('status', true)
                ->orderBy('sort')
                ->with(['items'])
                ->get();

            $grouped = [];

            foreach ($menus as $menu) {
                $location = $menu->location?->value ?? 'header';
                $grouped[$location] ??= [];
                $grouped[$location][] = MenuPresenter::forSite($menu);
            }

            return $grouped;
        });
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function forLocation(string $location): array
    {
        return $this->groupedByLocation()[$location] ?? [];
    }
}
