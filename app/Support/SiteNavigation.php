<?php

namespace App\Support;

use App\Models\User;

class SiteNavigation
{
    /**
     * @return array{items: array<int, array<string, mixed>>, cta: array<string, mixed>|null}
     */
    public static function forUser(?User $user): array
    {
        $config = config('site.navigation', []);
        $items = [];

        foreach ($config['items'] ?? [] as $item) {
            if (! self::isVisible($item, $user)) {
                continue;
            }

            $items[] = self::mapItem($item, $user);
        }

        $cta = $config['cta'] ?? null;
        if ($cta !== null && ! self::isVisible($cta, $user)) {
            $cta = null;
        }

        return [
            'items' => $items,
            'cta' => $cta ? [
                'label' => $cta['label'],
                'url' => $cta['url'],
            ] : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected static function mapItem(array $item, ?User $user): array
    {
        $type = $item['type'] ?? 'link';

        $mapped = [
            'id' => $item['id'],
            'label' => $item['label'],
            'url' => $item['url'],
            'type' => $type,
        ];

        if ($type === 'mega' && isset($item['mega']) && is_array($item['mega'])) {
            $mapped['mega'] = self::mapMega($item['mega'], $user);
        }

        return $mapped;
    }

    /**
     * @param  array<string, mixed>  $mega
     * @return array<string, mixed>
     */
    protected static function mapMega(array $mega, ?User $user): array
    {
        $columns = [];

        foreach ($mega['columns'] ?? [] as $column) {
            $links = [];

            foreach ($column['links'] ?? [] as $link) {
                if (! self::isVisible($link, $user)) {
                    continue;
                }

                $links[] = [
                    'label' => $link['label'],
                    'url' => $link['url'],
                    'description' => $link['description'] ?? null,
                ];
            }

            if ($links === []) {
                continue;
            }

            $columns[] = [
                'title' => $column['title'],
                'links' => $links,
            ];
        }

        $result = [
            'columns' => $columns,
        ];

        if (isset($mega['view_all']) && is_array($mega['view_all'])) {
            $result['view_all'] = [
                'label' => $mega['view_all']['label'],
                'url' => $mega['view_all']['url'],
            ];
        }

        if (isset($mega['featured']) && is_array($mega['featured'])) {
            $result['featured'] = [
                'title' => $mega['featured']['title'],
                'description' => $mega['featured']['description'] ?? null,
                'url' => $mega['featured']['url'],
                'image' => $mega['featured']['image'] ?? null,
            ];
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected static function isVisible(array $item, ?User $user): bool
    {
        $visibility = $item['visibility'] ?? 'all';

        if ($visibility === 'guest' && $user !== null) {
            return false;
        }

        if ($visibility === 'auth' && $user === null) {
            return false;
        }

        $roles = $item['roles'] ?? null;
        if ($roles !== null && $user === null) {
            return false;
        }

        if ($roles !== null && $user !== null && method_exists($user, 'hasAnyRole') && ! $user->hasAnyRole($roles)) {
            return false;
        }

        return true;
    }
}
