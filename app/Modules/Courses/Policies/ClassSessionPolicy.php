<?php

namespace App\Modules\Courses\Policies;

use App\Models\User;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Courses\Support\CourseOwnership;

class ClassSessionPolicy
{
    public function createAdobeRoom(User $user, ClassSession $classSession): bool
    {
        return $user->can('adobe-rooms-create')
            && $this->canManageCourse($user, $classSession);
    }

    public function syncAdobeRecordings(User $user, ClassSession $classSession): bool
    {
        return $user->can('adobe-recordings-create')
            && $this->canManageCourse($user, $classSession);
    }

    /**
     * ورود به کلاس مجازی Adobe برای جلسهٔ کلاسی که دانشجو در آن ثبت‌نام قطعی دارد.
     */
    public function joinAdobe(User $user, ClassSession $classSession): bool
    {
        return $this->joinSession($user, $classSession);
    }

    /**
     * ورود به کلاس آنلاین (Adobe Connect یا لینک ویدیو):
     * دانشجو با ثبت‌نام قطعی، یا استاد همان کلاس.
     */
    public function joinSession(User $user, ClassSession $classSession): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        $courseClass = $classSession->courseClass;

        if ($courseClass !== null
            && $user->hasRole('instructor')
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
            ->where('course_class_id', $classSession->course_class_id)
            ->exists();
    }

    /**
     * مشاهدهٔ حضور و غیاب فقط برای استادی که در class_instructors همان کلاس ثبت شده است.
     */
    public function viewAttendance(User $user, ClassSession $classSession): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        if (! $user->hasRole('instructor') || ! $user->can('instructor-panel-access')) {
            return false;
        }

        $courseClass = $classSession->courseClass;

        return $courseClass !== null && $courseClass->isTaughtBy($user);
    }

    private function canManageCourse(User $user, ClassSession $classSession): bool
    {
        $course = $classSession->courseClass?->course;

        return $course !== null && CourseOwnership::canManage($user, $course);
    }
}
