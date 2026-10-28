<?php

namespace App\Modules\Courses\Services;

use App\Modules\AdobeConnect\Services\SyncAdobeConnectEnrollmentService;
use App\Modules\Courses\Models\Enrollment;

class EnrollmentStudentAccessService
{
    public function __construct(
        private readonly SyncAdobeConnectEnrollmentService $adobeEnrollmentSync,
    ) {}

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

        $enrollment->loadMissing('courseClass');
        $this->adobeEnrollmentSync->sync($enrollment);
    }
}
