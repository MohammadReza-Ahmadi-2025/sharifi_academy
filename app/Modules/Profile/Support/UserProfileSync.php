<?php

namespace App\Modules\Profile\Support;

use App\Models\User;

class UserProfileSync
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public static function sync(User $user, array $validated): void
    {
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'headline' => $validated['headline'] ?? null,
                'bio' => $validated['bio'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'national_code' => $validated['national_code'] ?? null,
                'province_city_id' => $validated['province_city_id'] ?? null,
                'is_public' => (bool) ($validated['is_public'] ?? true),
            ],
        );
    }

    /**
     * @return list<string>
     */
    public static function attributeKeys(): array
    {
        return [
            'headline',
            'bio',
            'birth_date',
            'national_code',
            'province_city_id',
            'is_public',
        ];
    }
}
