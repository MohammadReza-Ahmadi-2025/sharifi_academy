<?php

namespace App\Modules\Roles\Support;

use App\Modules\Roles\Models\Role;
use App\Models\User;

class RoleGuard
{
    public static function isSystemRole(string $name): bool
    {
        return in_array($name, config('permissions.system_roles', []), true);
    }

    public static function canDelete(User $user, Role $role): bool
    {
        if (! $user->can('roles-delete')) {
            return false;
        }

        if (self::isSystemRole($role->name)) {
            return false;
        }

        return $role->users()->count() === 0;
    }

    public static function canUpdate(User $user, Role $role): bool
    {
        if (! $user->can('roles-update')) {
            return false;
        }

        if ($role->name === 'super-admin' && ! $user->hasRole('super-admin')) {
            return false;
        }

        return true;
    }

    /**
     * @param  list<string>  $permissionNames
     * @return list<string>
     */
    public static function filterAssignablePermissions(User $user, array $permissionNames): array
    {
        if ($user->hasRole('super-admin')) {
            return $permissionNames;
        }

        return array_values(array_filter(
            $permissionNames,
            fn (string $name) => $user->can($name),
        ));
    }
}
