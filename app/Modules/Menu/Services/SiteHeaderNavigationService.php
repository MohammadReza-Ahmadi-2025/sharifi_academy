<?php

namespace App\Modules\Menu\Services;

use App\Modules\Menu\Enums\MenuLocation;

class SiteHeaderNavigationService
{
    public function __construct(
        private readonly SiteMenuService $menuService,
    ) {}

    /**
     * @return array{items: list<array<string, mixed>>, cta: null}
     */
    public function build(): array
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
            'items' => $items,
            'cta' => null,
        ];
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
