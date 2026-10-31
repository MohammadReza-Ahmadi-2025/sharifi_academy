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
     * مشاهده جزئیات ثبت‌نام خود (جلسات پس از تأیید؛ وضعیت pending هم قابل مشاهده است).
     */
    public function view(User $user, Enrollment $enrollment): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        if ($user->hasRole('super-admin')) {
            return true;
        }

        return (int) $enrollment->user_id === (int) $user->id;
    }
}
