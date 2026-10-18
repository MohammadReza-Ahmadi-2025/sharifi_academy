<?php

namespace App\Modules\Profile\Support;

use App\Models\User;

class UserProfileCompleteness
{
    public static function isComplete(User $user): bool
    {
        if (! filled($user->name) || ! filled($user->family)) {
            return false;
        }

        $user->loadMissing('profile');

        return $user->profile?->province_city_id !== null;
    }

    public static function enrollmentBlockMessage(): string
    {
        return 'کاربر باید نام، نام خانوادگی و شهر را در پروفایل داشته باشد. ابتدا از بخش کاربران پروفایل را تکمیل کنید.';
    }
}
