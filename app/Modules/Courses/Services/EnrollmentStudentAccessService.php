<?php

namespace App\Modules\Courses\Services;

use App\Modules\Courses\Models\Enrollment;

class EnrollmentStudentAccessService
{
    public function grantStudentRoleIfConfirmed(Enrollment $enrollment): void
    {
        if (! $enrollment->isConfirmed()) {
            return;
        }

        $user = $enrollment->user;
        if ($user === null) {
            return;
        }

        if (! $user->hasRole('student')) {
            $user->assignRole('student');
        }
    }
}
