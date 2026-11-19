<?php

namespace App\Support;

use App\Models\User;
use App\Modules\Profile\Support\UserProfileCompleteness;

class MobileTabBar
{
    /**
     * @return array{can_create_post: bool, create_post_url: string|null, needs_profile: bool}
     */
    public static function forUser(?User $user): array
    {
        if ($user === null) {
            return [
                'can_create_post' => false,
                'create_post_url' => '/login',
                'needs_profile' => false,
            ];
        }

        if (! UserProfileCompleteness::isComplete($user)) {
            return [
                'can_create_post' => false,
                'create_post_url' => route('site.profile.edit'),
                'needs_profile' => true,
            ];
        }

        return [
            'can_create_post' => true,
            'create_post_url' => route('account.posts.create'),
            'needs_profile' => false,
        ];
    }
}
