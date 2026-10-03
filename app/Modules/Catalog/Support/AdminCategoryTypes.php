<?php

namespace App\Modules\Catalog\Support;

use App\Models\User;
use App\Modules\Catalog\Models\Category;

class AdminCategoryTypes
{
    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return [
            Category::TYPE_PRODUCT,
            Category::TYPE_BLOG,
            Category::TYPE_COURSE,
            Category::TYPE_FAQ,
            Category::TYPE_SERVICE,
        ];
    }

    /**
     * @return array<int, array{value: string, label: string, icon: string}>
     */
    public static function optionsFor(?User $user): array
    {
        $options = [];

        foreach (self::definitions() as $type => $definition) {
            if ($user !== null && ! self::canIndex($user, $type)) {
                continue;
            }

            $options[] = [
                'value' => $type,
                'label' => $definition['label'],
                'icon' => $definition['icon'],
            ];
        }

        return $options;
    }

    public static function label(string $type): string
    {
        return self::definitions()[$type]['label'] ?? $type;
    }

    public static function permissionPrefix(string $type): string
    {
        return self::definitions()[$type]['permission_prefix'] ?? 'catalog-categories';
    }

    public static function canIndex(?User $user, string $type): bool
    {
        return $user?->can(self::permissionPrefix($type).'-index') ?? false;
    }

    public static function canCreate(?User $user, string $type): bool
    {
        return $user?->can(self::permissionPrefix($type).'-create') ?? false;
    }

    public static function canUpdate(?User $user, string $type): bool
    {
        return $user?->can(self::permissionPrefix($type).'-update') ?? false;
    }

    public static function canDelete(?User $user, string $type): bool
    {
        return $user?->can(self::permissionPrefix($type).'-delete') ?? false;
    }

    public static function canSeeAnyNav(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        foreach (self::values() as $type) {
            if (self::canIndex($user, $type)) {
                return true;
            }
        }

        return false;
    }

    public static function resolveType(?string $type): string
    {
        $type = $type ?: Category::TYPE_PRODUCT;

        if (! in_array($type, self::values(), true)) {
            abort(404);
        }

        return $type;
    }

    /**
     * @return array<string, array{label: string, icon: string, permission_prefix: string}>
     */
    private static function definitions(): array
    {
        return [
            Category::TYPE_PRODUCT => [
                'label' => 'محصول',
                'icon' => 'package',
                'permission_prefix' => 'catalog-categories',
            ],
            Category::TYPE_BLOG => [
                'label' => 'وبلاگ',
                'icon' => 'newspaper',
                'permission_prefix' => 'blog-categories',
            ],
            Category::TYPE_COURSE => [
                'label' => 'دوره',
                'icon' => 'graduation-cap',
                'permission_prefix' => 'catalog-categories',
            ],
            Category::TYPE_FAQ => [
                'label' => 'سوالات متداول',
                'icon' => 'help-circle',
                'permission_prefix' => 'catalog-categories',
            ],
            Category::TYPE_SERVICE => [
                'label' => 'خدمات',
                'icon' => 'briefcase',
                'permission_prefix' => 'catalog-categories',
            ],
        ];
    }
}
