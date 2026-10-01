<?php

namespace App\Modules\Profile\Support;

use App\Models\User;
use App\Modules\Roles\Support\PermissionCatalog;
use App\Modules\Users\Support\UserAvatarMedia;

class ProfilePayload
{
    /**
     * @return array<string, mixed>
     */
    public static function forUser(User $user): array
    {
        $user->loadMissing(['media', 'profile.provinceCity.parent']);

        $extended = $user->profile;
        $city = $extended?->provinceCity;

        return [
            'name' => $user->name,
            'family' => $user->family,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'gender' => $user->gender,
            'avatar' => UserAvatarMedia::payload($user),
            'emailVerified' => $user->email_verified_at !== null,
            'mobileVerified' => $user->mobile_verified_at !== null,
            'lastLoginAt' => $user->last_login_at?->toIso8601String(),
            'lastLoginIp' => $user->last_login_ip,
            'createdAt' => $user->created_at?->toIso8601String(),
            'roles' => $user->getRoleNames()->values()->all(),
            'roleLabels' => collect($user->getRoleNames())
                ->map(fn (string $role) => PermissionCatalog::labelForRole($role))
                ->values()
                ->all(),
            'extended' => [
                'headline' => $extended?->headline,
                'bio' => $extended?->bio,
                'birthDate' => $extended?->birth_date?->format('Y-m-d'),
                'nationalCode' => $extended?->national_code,
                'provinceCityId' => $extended?->province_city_id,
                'provinceId' => $city?->parent_id,
                'cityTitle' => $city?->title,
                'provinceTitle' => $city?->parent?->title ?? $city?->title,
                'isPublic' => $extended?->is_public ?? true,
                'isVerified' => $extended?->is_verified ?? false,
            ],
        ];
    }
}
