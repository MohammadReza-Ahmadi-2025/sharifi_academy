<?php

namespace App\Modules\Users\Support;

use App\Modules\Roles\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;

class UserGuard
{
    public static function canUpdate(User $actor, User $target): bool
    {
        if (! $actor->can('users-update')) {
            return false;
        }

        if ($target->hasRole('super-admin') && ! $actor->hasRole('super-admin')) {
            return false;
        }

        return true;
    }

    public static function canDelete(User $actor, User $target): bool
    {
        if (! $actor->can('users-delete')) {
            return false;
        }

        if ($actor->id === $target->id) {
            return false;
        }

        if ($target->hasRole('super-admin')) {
            if (! $actor->hasRole('super-admin')) {
                return false;
            }

            return self::superAdminCount() > 1;
        }

        return true;
    }

    /**
     * @return Collection<int, Role>
     */
    public static function assignableRoles(User $actor): Collection
    {
        $query = Role::query()->where('guard_name', 'web')->orderBy('name');

        if (! $actor->hasRole('super-admin')) {
            $query->where('name', '!=', 'super-admin');
        }

        return $query->get();
    }

    /**
     * @param  list<string>  $roleNames
     * @return list<string>
     */
    public static function filterAssignableRoles(User $actor, array $roleNames): array
    {
        $allowed = self::assignableRoles($actor)->pluck('name')->all();

        return array_values(array_intersect($roleNames, $allowed));
    }

    public static function superAdminCount(): int
    {
        return User::query()
            ->role('super-admin')
            ->count();
    }
}
