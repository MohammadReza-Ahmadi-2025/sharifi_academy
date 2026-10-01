<?php

namespace App\Modules\Roles\Support;

use App\Modules\Roles\Models\Role;

class DefaultRolePermissions
{
    /**
     * پرمیشن‌های پیش‌فرض نقش‌های سیستمی را در دیتابیس sync می‌کند.
     */
    public static function sync(): void
    {
        PermissionCatalog::syncToDatabase();

        foreach (self::map() as $roleName => $permissions) {
            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', 'web')
                ->first();

            if ($role === null) {
                continue;
            }

            $role->syncPermissions($permissions);
        }
    }

    /**
     * @return array<string, list<string>>
     */
    public static function map(): array
    {
        $all = PermissionCatalog::allSlugs();

        return [
            'super-admin' => $all,
            'admin' => array_values(array_filter(
                $all,
                static fn (string $name): bool => ! in_array($name, ['roles-delete', 'users-delete'], true),
            )),
        ];
    }
}
