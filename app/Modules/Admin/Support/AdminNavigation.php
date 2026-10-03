<?php

namespace App\Modules\Admin\Support;

use App\Models\User;

class AdminNavigation
{
    /**
     * @return array<int, array{id: string, label: string, href: string}>
     */
    public static function forUser(?User $user): array
    {
        if ($user === null) {
            return [];
        }

        $items = [];

        foreach (config('admin.navigation', []) as $item) {
            if (! self::canSee($user, $item)) {
                continue;
            }

            $items[] = [
                'id' => $item['id'],
                'label' => $item['label'],
                'href' => $item['href'],
                'icon' => $item['icon'] ?? $item['id'],
            ];
        }

        return $items;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public static function canSee(User $user, array $item): bool
    {
        if (isset($item['callback']) && is_string($item['callback']) && is_callable($item['callback'])) {
            return (bool) call_user_func($item['callback'], $user);
        }

        $permissionsAny = $item['permissions_any'] ?? null;

        if (is_array($permissionsAny) && $permissionsAny !== []) {
            foreach ($permissionsAny as $permission) {
                if ($user->can($permission)) {
                    return true;
                }
            }

            return false;
        }

        $permission = $item['permission'] ?? null;

        if ($permission !== null && $user->can($permission)) {
            return true;
        }

        $roles = $item['roles'] ?? null;

        if (is_array($roles) && $roles !== [] && $user->hasAnyRole($roles)) {
            return true;
        }

        if ($permission === null && $roles === null) {
            return $user->can('admin-panel-access') || $user->hasAnyRole(['super-admin', 'admin']);
        }

        return false;
    }

    public static function canAccessPanel(User $user): bool
    {
        if ($user->can('admin-panel-access') || $user->hasAnyRole(['super-admin', 'admin'])) {
            return true;
        }

        foreach (config('admin.navigation', []) as $item) {
            if (self::canSee($user, $item)) {
                return true;
            }
        }

        return false;
    }
}
