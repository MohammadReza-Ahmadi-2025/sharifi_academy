<?php

namespace App\Modules\Menu\Services;

use App\Models\User;
use App\Modules\Menu\Enums\MenuLocation;

class SiteHeaderNavigationService
{
    public function __construct(
        private readonly SiteMenuService $menuService,
    ) {}

    /**
     * @return array{items: list<array<string, mixed>>, cta: null}
     */
    public function build(?User $user = null): array
    {
        $menus = $this->menuService->forLocation(MenuLocation::Header->value);
        $items = [];

        foreach ($menus as $menu) {
            $mapped = $this->mapMenu($menu);
            if ($mapped !== null) {
                $items[] = $mapped;
            }
        }

        return [
            'items' => $this->applyAuthNav($items, $user),
            'cta' => null,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    private function applyAuthNav(array $items, ?User $user): array
    {
        if ($user === null) {
            return $items;
        }

        return array_values(array_filter(
            $items,
            fn (array $item): bool => ! $this->isGuestAuthNavItem($item),
        ));
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function isGuestAuthNavItem(array $item): bool
    {
        $url = trim((string) ($item['url'] ?? ''));
        if ($url === '/login' || $url === '/register') {
            return true;
        }

        return str_starts_with($url, '/login?') || str_starts_with($url, '/register?');
    }

    /**
     * @param  array<string, mixed>  $menu
     * @return array<string, mixed>|null
     */
    private function mapMenu(array $menu): ?array
    {
        $title = trim((string) ($menu['title'] ?? ''));
        if ($title === '') {
            return null;
        }

        $id = 'menu-'.($menu['id'] ?? 0);
        $menuUrl = filled($menu['url'] ?? null) ? (string) $menu['url'] : '#';
        $children = is_array($menu['items'] ?? null) ? $menu['items'] : [];

        if ($children === []) {
            return [
                'id' => $id,
                'label' => $title,
                'url' => $menuUrl,
                'type' => 'link',
            ];
        }

        $links = [];

        foreach ($children as $child) {
            if (! is_array($child)) {
                continue;
            }

            $childTitle = trim((string) ($child['title'] ?? ''));
            if ($childTitle === '') {
                continue;
            }

            $links[] = [
                'label' => $childTitle,
                'url' => (string) ($child['url'] ?? '#'),
                'description' => null,
            ];
        }

        if ($links === []) {
            return [
                'id' => $id,
                'label' => $title,
                'url' => $menuUrl,
                'type' => 'link',
            ];
        }

        $mega = [
            'columns' => [
                [
                    'title' => $title,
                    'links' => $links,
                ],
            ],
        ];

        if ($menuUrl !== '#') {
            $mega['view_all'] = [
                'label' => 'مشاهده همه',
                'url' => $menuUrl,
            ];
        }

        return [
            'id' => $id,
            'label' => $title,
            'url' => $menuUrl,
            'type' => 'mega',
            'mega' => $mega,
        ];
    }
}
