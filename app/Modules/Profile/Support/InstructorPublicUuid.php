<?php

namespace App\Modules\Profile\Support;

use App\Models\User;
use App\Modules\Profile\Models\UserProfile;
use Illuminate\Support\Str;

final class InstructorPublicUuid
{
    public static function ensureFor(User $user): string
    {
        $profile = ResumeModeration::ensureProfile($user);
        $existing = is_string($profile->uuid) ? trim($profile->uuid) : '';

        if ($existing !== '' && Str::isUuid($existing)) {
            return $existing;
        }

        do {
            $uuid = (string) Str::uuid();
        } while (UserProfile::query()->where('uuid', $uuid)->exists());

        $profile->forceFill(['uuid' => $uuid])->save();

        return $uuid;
    }

    public static function routeKey(User $user): string
    {
        return self::ensureFor($user);
    }

    public static function findUserByPublicUuid(string $value): ?User
    {
        $decoded = trim(urldecode($value));
        if ($decoded === '' || ! Str::isUuid($decoded)) {
            return null;
        }

        $profile = UserProfile::query()
            ->whereRaw('LOWER(uuid) = ?', [Str::lower($decoded)])
            ->first();

        return $profile?->user;
    }
}
