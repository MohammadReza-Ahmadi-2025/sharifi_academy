<?php

namespace App\Modules\Courses\Policies;

use App\Models\User;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Support\CourseOwnership;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('courses-index');
    }

    public function view(User $user, Course $course): bool
    {
        return $user->can('courses-index') && CourseOwnership::canManage($user, $course);
    }

    public function create(User $user): bool
    {
        return $user->can('courses-create');
    }

    public function update(User $user, Course $course): bool
    {
        return $user->can('courses-update') && CourseOwnership::canManage($user, $course);
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->can('courses-delete') && CourseOwnership::canManage($user, $course);
    }
}
