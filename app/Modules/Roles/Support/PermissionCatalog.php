<?php

namespace App\Modules\Roles\Support;

use App\Modules\Roles\Models\Permission;

class PermissionCatalog
{
    /**
     * @return array<int, array{id: string, label: string, permissions: array<int, array{slug: string, title: string}>}>
     */
    public static function groupsForUi(): array
    {
        $groups = [];

        foreach (config('permissions.groups', []) as $id => $group) {
            $permissions = [];

            foreach ($group['permissions'] ?? [] as $slug => $title) {
                $permissions[] = [
                    'slug' => $slug,
                    'title' => $title,
                ];
            }

            $groups[] = [
                'id' => $id,
                'label' => $group['label'] ?? $id,
                'permissions' => $permissions,
            ];
        }

        return $groups;
    }

    /**
     * @return list<string>
     */
    public static function allSlugs(): array
    {
        $slugs = [];

        foreach (config('permissions.groups', []) as $group) {
            foreach (array_keys($group['permissions'] ?? []) as $slug) {
                $slugs[] = $slug;
            }
        }

        return array_values(array_unique($slugs));
    }

    public static function syncToDatabase(): void
    {
        foreach (self::allSlugs() as $name) {
            Permission::query()->firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }
    }

    public static function labelForRole(string $name): string
    {
        return config("permissions.role_labels.{$name}") ?? $name;
    }
}
