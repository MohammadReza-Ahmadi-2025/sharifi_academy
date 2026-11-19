<?php

namespace App\Modules\Courses\Policies;

use App\Models\User;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Courses\Support\CourseOwnership;
use App\Modules\Profile\Support\UserProfileCompleteness;

class CourseClassPolicy
{
    public function createAdobeRoom(User $user, CourseClass $courseClass): bool
    {
        return $user->can('adobe-rooms-create')
            && $courseClass->course !== null
            && CourseOwnership::canManage($user, $courseClass->course);
    }

    /**
     * ورود مستقیم به اتاق Adobe سطح کلاس (حتی بدون جلسه).
     */
    public function joinClass(User $user, CourseClass $courseClass): bool
    {
        $courseClass->loadMissing('course');

        if ($courseClass->course !== null
            && CourseOwnership::canManage($user, $courseClass->course)) {
            return true;
        }

        if ($user->hasRole('instructor')
            && $user->can('instructor-panel-access')
            && $courseClass->isTaughtBy($user)) {
            return true;
        }

        if (! $user->hasRole('student') || ! $user->can('student-panel-access')) {
            return false;
        }

        return Enrollment::query()
            ->confirmed()
            ->where('user_id', $user->id)
            ->where('course_class_id', $courseClass->id)
            ->exists();
    }

    /**
     * درخواست ثبت‌نام از سایت برای کلاس عمومی و باز.
     */
    public function requestEnrollment(User $user, CourseClass $courseClass): bool
    {
        if (! UserProfileCompleteness::isComplete($user)) {
            return false;
        }

        if (! $courseClass->is_public) {
            return false;
        }

        if (! $this->isOpenForRegistration($courseClass)) {
            return false;
        }

        if ($this->hasActiveEnrollment($user, $courseClass)) {
            return false;
        }

        if ($this->isAtCapacity($courseClass)) {
            return false;
        }

        return true;
    }

    private function isOpenForRegistration(CourseClass $courseClass): bool
    {
        return CourseClass::query()
            ->whereKey($courseClass->id)
            ->public()
            ->openForRegistration()
            ->exists();
    }

    private function hasActiveEnrollment(User $user, CourseClass $courseClass): bool
    {
        return $courseClass->enrollments()
            ->where('user_id', $user->id)
            ->whereIn('status', [
                EnrollmentStatus::Pending->value,
                EnrollmentStatus::Confirmed->value,
            ])
            ->exists();
    }

    private function isAtCapacity(CourseClass $courseClass): bool
    {
        if ($courseClass->capacity === null || $courseClass->capacity <= 0) {
            return false;
        }

        $occupied = $courseClass->enrollments()
            ->whereIn('status', [
                EnrollmentStatus::Pending->value,
                EnrollmentStatus::Confirmed->value,
            ])
            ->count();

        return $occupied >= $courseClass->capacity;
    }
}
