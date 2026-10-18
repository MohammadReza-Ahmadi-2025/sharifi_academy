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
    /**
     * نوع‌هایی که در پنل «دسته‌بندی» (دوره / وبلاگ) مدیریت می‌شوند.
     *
     * @return list<string>
     */
    public static function managedTypes(): array
    {
        return [
            Category::TYPE_COURSE,
            Category::TYPE_BLOG,
        ];
    }

    public static function optionsFor(?User $user): array
    {
        return self::optionsForTypes($user, self::managedTypes());
    }

    /**
     * @param  list<string>  $types
     * @return array<int, array{value: string, label: string, icon: string}>
     */
    public static function creatableOptionsFor(?User $user): array
    {
        return self::optionsForTypes($user, self::managedTypes(), requireCreate: true);
    }

    /**
     * @param  list<string>  $types
     * @return array<int, array{value: string, label: string, icon: string}>
     */
    private static function optionsForTypes(?User $user, array $types, bool $requireCreate = false): array
    {
        $options = [];

        foreach ($types as $type) {
            $definition = self::definitions()[$type] ?? null;
            if ($definition === null) {
                continue;
            }

            if ($user !== null) {
                if ($requireCreate && ! self::canCreate($user, $type)) {
                    continue;
                }
                if (! $requireCreate && ! self::canIndex($user, $type)) {
                    continue;
                }
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

        foreach (self::managedTypes() as $type) {
            if (self::canIndex($user, $type)) {
                return true;
            }
        }

        return false;
    }

    public static function resolveType(?string $type): string
    {
        $type = $type ?: Category::TYPE_COURSE;

        if (! in_array($type, self::values(), true)) {
            abort(404);
        }

        return $type;
    }

    public static function resolveManagedType(?string $type): string
    {
        $type = self::resolveType($type);

        if (! in_array($type, self::managedTypes(), true)) {
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
