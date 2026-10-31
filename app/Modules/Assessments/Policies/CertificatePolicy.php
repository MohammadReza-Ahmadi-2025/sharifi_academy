<?php

namespace App\Modules\Assessments\Policies;

use App\Models\User;
use App\Modules\Assessments\Models\Certificate;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Support\CourseOwnership;

class CertificatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('student-panel-access') || $user->can('certificates-issue');
    }

    public function view(User $user, Certificate $certificate): bool
    {
        if ((int) $certificate->user_id === (int) $user->id) {
            return true;
        }

        return $user->can('certificates-issue')
            && CourseOwnership::canManage($user, $certificate->course);
    }

    public function issue(User $user, Course $course): bool
    {
        return $user->can('certificates-issue') && CourseOwnership::canManage($user, $course);
    }
}
