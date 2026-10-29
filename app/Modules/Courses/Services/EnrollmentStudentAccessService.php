<?php

namespace App\Modules\Courses\Services;

use App\Modules\AdobeConnect\Services\SyncAdobeConnectEnrollmentService;
use App\Modules\AdobeConnect\Support\AdobeEnrollmentSyncResult;
use App\Modules\Courses\Models\Enrollment;

class EnrollmentStudentAccessService
{
    public function __construct(
        private readonly SyncAdobeConnectEnrollmentService $adobeEnrollmentSync,
    ) {}

    public function grantStudentRoleIfConfirmed(Enrollment $enrollment): ?AdobeEnrollmentSyncResult
    {
        if (! $enrollment->isConfirmed()) {
            return null;
        }

        $user = $enrollment->user;
        if ($user === null) {
            return null;
        }

        if (! $user->hasRole('student')) {
            $user->assignRole('student');
        }

        $enrollment->loadMissing('courseClass');

        return $this->adobeEnrollmentSync->sync($enrollment);
    }
}
