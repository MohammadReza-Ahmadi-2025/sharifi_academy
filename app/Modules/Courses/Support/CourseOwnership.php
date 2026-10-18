<?php

namespace App\Modules\Courses\Support;

use App\Models\User;
use App\Modules\Courses\Models\Course;
use Illuminate\Database\Eloquent\Builder;

final class CourseOwnership
{
    public static function managesAllCourses(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    public static function owns(User $user, Course $course): bool
    {
        return $course->created_by !== null && (int) $course->created_by === (int) $user->id;
    }

    public static function canManage(User $user, Course $course): bool
    {
        return self::managesAllCourses($user) || self::owns($user, $course);
    }

    /**
     * @param  Builder<Course>  $query
     * @return Builder<Course>
     */
    public static function scopeForManager(Builder $query, User $user): Builder
    {
        if (self::managesAllCourses($user)) {
            return $query;
        }

        return $query->where('created_by', $user->id);
    }
}
