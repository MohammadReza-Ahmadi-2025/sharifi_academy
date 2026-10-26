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
     *     panel: array{label: string, url: string}|null,
     *     panels: list<array{label: string, url: string}>
     * }
     */
    public static function forUser(User $user): array
    {
        $panels = self::panelsForUser($user);
        $roleLabels = self::roleLabelsForUser($user);

        return [
            'display_name' => self::displayName($user),
            'role' => self::primaryRole($user),
            'role_label' => $roleLabels !== [] ? implode(' · ', $roleLabels) : 'کاربر',
            'profile_url' => route('site.profile.edit'),
            'panel' => $panels[0] ?? null,
            'panels' => $panels,
        ];
    }

    /**
     * @return list<array{label: string, url: string}>
     */
    private static function panelsForUser(User $user): array
    {
        $panels = [];
        $seenUrls = [];

        foreach (self::ROLE_ORDER as $roleName) {
            if (! $user->hasRole($roleName) || ! self::canAccessPanel($user, $roleName)) {
                continue;
            }

            $link = self::panelLink($roleName);
            if ($link === null || isset($seenUrls[$link['url']])) {
                continue;
            }

            $seenUrls[$link['url']] = true;
            $panels[] = $link;
        }

        return $panels;
    }

    /**
     * @return list<string>
     */
    private static function roleLabelsForUser(User $user): array
    {
        $labels = [];

        foreach (self::ROLE_ORDER as $roleName) {
            if ($user->hasRole($roleName)) {
                $labels[] = self::ROLE_LABELS[$roleName] ?? $roleName;
            }
        }

        return $labels;
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

    private static function canAccessPanel(User $user, string $role): bool
    {
        return match ($role) {
            'super-admin', 'admin' => $user->can('admin-panel-access') || $user->hasAnyRole(['super-admin', 'admin']),
            'instructor' => $user->hasRole('instructor') && $user->can('instructor-panel-access'),
            'student' => $user->hasRole('student') && $user->can('student-panel-access'),
            default => false,
        };
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
