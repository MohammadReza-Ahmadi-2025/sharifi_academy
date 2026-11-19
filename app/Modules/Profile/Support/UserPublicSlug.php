<?php

namespace App\Modules\Profile\Support;

use App\Models\User;
use App\Support\SeoSlug;

final class UserPublicSlug
{
    public static function forUser(User $user): string
    {
        $name = trim(($user->name ?? '').' '.($user->family ?? ''));
        $slug = SeoSlug::normalize($name);

        return $slug !== '' ? $slug : 'user';
    }

    public static function profileUrl(User $user, ?string $uuid = null): string
    {
        $uuid ??= InstructorPublicUuid::routeKey($user);

        return route('users.show', [
            'uuid' => $uuid,
            'slug' => self::forUser($user),
        ]);
    }
}
