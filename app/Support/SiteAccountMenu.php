<?php

namespace App\Support;

use App\Models\User;

class SiteAccountMenu
{
    private const ROLE_ORDER = ['super-admin', 'admin', 'instructor', 'student'];

    private const ROLE_LABELS = [
        'super-admin' => 'مدیر سیستم',
        'admin' => 'مدیر',
        'instructor' => 'استاد',
        'student' => 'دانشجو',
    ];

    /**
     * @return array{
     *     display_name: string,
     *     role: string,
     *     role_label: string,
     *     profile_url: string,
     *     panel: array{label: string, url: string}|null
     * }
     */
    public static function forUser(User $user): array
    {
        $role = self::primaryRole($user);

        return [
            'display_name' => self::displayName($user),
            'role' => $role,
            'role_label' => self::ROLE_LABELS[$role] ?? 'کاربر',
            'profile_url' => route('site.profile.edit'),
            'panel' => self::panelLink($role),
        ];
    }

    private static function primaryRole(User $user): string
    {
        foreach (self::ROLE_ORDER as $roleName) {
            if ($user->hasRole($roleName)) {
                return $roleName;
            }
        }

        $first = $user->getRoleNames()->first();

        return $first !== null ? (string) $first : 'student';
    }

    private static function displayName(User $user): string
    {
        $name = trim(implode(' ', array_filter([(string) ($user->name ?? ''), (string) ($user->family ?? '')])));
        if ($name !== '') {
            return $name;
        }

        if (filled($user->mobile)) {
            return (string) $user->mobile;
        }

        if (filled($user->email)) {
            return (string) $user->email;
        }

        return 'حساب من';
    }

    /**
     * @return array{label: string, url: string}|null
     */
    private static function panelLink(string $role): ?array
    {
        return match ($role) {
            'super-admin', 'admin' => [
                'label' => 'ورود به پنل ادمین',
                'url' => '/admin',
            ],
            'instructor' => [
                'label' => 'ورود به پنل استاد',
                'url' => '/instructor',
            ],
            'student' => [
                'label' => 'ورود به پنل دانشجو',
                'url' => route('student.dashboard'),
            ],
            default => null,
        };
    }
}
