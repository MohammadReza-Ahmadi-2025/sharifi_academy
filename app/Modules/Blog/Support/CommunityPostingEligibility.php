<?php

namespace App\Modules\Blog\Support;

use App\Models\User;
use App\Modules\Blog\Enums\PostKind;
use App\Modules\Blog\Models\Post;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Profile\Support\UserProfileCompleteness;

final class CommunityPostingEligibility
{
    public static function canCreate(User $user): bool
    {
        if ($user->can('blog-posts-create')) {
            return true;
        }

        if (! UserProfileCompleteness::isComplete($user)) {
            return false;
        }

        if (self::isInstructor($user)) {
            return true;
        }

        return self::hasConfirmedEnrollment($user);
    }

    public static function isInstructorOrAdmin(User $user): bool
    {
        return $user->can('blog-posts-create') || self::isInstructor($user);
    }

    public static function isInstructor(User $user): bool
    {
        return $user->hasRole('instructor');
    }

    public static function hasConfirmedEnrollment(User $user): bool
    {
        return Enrollment::query()
            ->where('user_id', $user->id)
            ->where('status', EnrollmentStatus::Confirmed)
            ->exists();
    }

    public static function hasAuthoredCommunityPost(User $user): bool
    {
        return Post::query()
            ->where('author_id', $user->id)
            ->where('kind', PostKind::Community->value)
            ->exists();
    }

    public static function canManageOwnCommunityPosts(User $user): bool
    {
        return self::canCreate($user)
            || self::hasAuthoredCommunityPost($user);
    }
}
