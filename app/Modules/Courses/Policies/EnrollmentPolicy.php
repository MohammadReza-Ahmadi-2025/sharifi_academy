<?php

namespace App\Modules\Courses\Policies;

use App\Models\User;
use App\Modules\Courses\Models\Enrollment;

class EnrollmentPolicy
{
    /**
     * لیست ثبت‌نام‌های خود در پنل دانشجو.
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->hasRole('student') && $user->can('student-panel-access');
    }

    /**
     * مشاهده جزئیات ثبت‌نام (جلسات، Adobe و …) فقط برای مالک و ثبت‌نام قطعی.
     */
    public function view(User $user, Enrollment $enrollment): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        if ($user->hasRole('super-admin')) {
            return $enrollment->isConfirmed();
        }

        return $enrollment->user_id === $user->id && $enrollment->isConfirmed();
    }
}
