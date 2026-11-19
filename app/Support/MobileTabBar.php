<?php

namespace App\Support;

use App\Models\User;
use App\Modules\Blog\Support\CommunityPostingEligibility;
use App\Modules\Profile\Support\UserProfileCompleteness;

class MobileTabBar
{
    /**
     * @return array{
     *     can_create_post: bool,
     *     create_post_url: string|null,
     *     needs_profile: bool,
     *     needs_enrollment: bool
     * }
     */
    public static function forUser(?User $user): array
    {
        if ($user === null) {
            return [
                'can_create_post' => false,
                'create_post_url' => '/login',
                'needs_profile' => false,
                'needs_enrollment' => false,
            ];
        }

        if (! UserProfileCompleteness::isComplete($user)) {
            return [
                'can_create_post' => false,
                'create_post_url' => route('site.profile.edit'),
                'needs_profile' => true,
                'needs_enrollment' => false,
            ];
        }

        if (CommunityPostingEligibility::canCreate($user)) {
            return [
                'can_create_post' => true,
                'create_post_url' => route('account.posts.create'),
                'needs_profile' => false,
                'needs_enrollment' => false,
            ];
        }

        return [
            'can_create_post' => false,
            'create_post_url' => '/',
            'needs_profile' => false,
            'needs_enrollment' => true,
        ];
    }
}
