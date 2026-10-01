<?php

namespace App\Modules\Users\Support;

use App\Models\User;
use App\Modules\Roles\Support\PermissionCatalog;

class UserPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forList(User $user, ?User $actor): array
    {
        $roles = $user->roles->map(fn ($role) => [
            'name' => $role->name,
            'label' => PermissionCatalog::labelForRole($role->name),
        ])->values()->all();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'family' => $user->family,
            'fullName' => trim(($user->name ?? '').' '.($user->family ?? '')) ?: '—',
            'mobile' => $user->mobile,
            'email' => $user->email,
            'gender' => $user->gender,
            'genderLabel' => self::genderLabel($user->gender),
            'status' => (int) $user->status,
            'statusLabel' => (int) $user->status === 1 ? 'فعال' : 'غیرفعال',
            'roles' => $roles,
            'avatar' => UserAvatarMedia::payload($user),
            'createdAt' => $user->created_at?->toIso8601String(),
            'lastLoginAt' => $user->last_login_at?->toIso8601String(),
            'canUpdate' => $actor ? UserGuard::canUpdate($actor, $user) : false,
            'canDelete' => $actor ? UserGuard::canDelete($actor, $user) : false,
        ];
    }

    /**
     * @return array<int, array{name: string, label: string}>
     */
    public static function roleOptions(User $actor): array
    {
        return UserGuard::assignableRoles($actor)
            ->map(fn ($role) => [
                'name' => $role->name,
                'label' => PermissionCatalog::labelForRole($role->name),
            ])
            ->values()
            ->all();
    }

    public static function genderLabel(?string $gender): ?string
    {
        return match ($gender) {
            'male' => 'مرد',
            'female' => 'زن',
            default => null,
        };
    }
}
