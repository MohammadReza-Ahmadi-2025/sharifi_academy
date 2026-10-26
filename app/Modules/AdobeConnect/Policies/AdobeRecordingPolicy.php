<?php

namespace App\Modules\AdobeConnect\Policies;

use App\Models\User;
use App\Modules\AdobeConnect\Models\AdobeRecording;
use App\Modules\Courses\Support\CourseOwnership;

class AdobeRecordingPolicy
{
    public function view(User $user, AdobeRecording $recording): bool
    {
        return $user->can('adobe-recordings-index') && $this->canManageCourse($user, $recording);
    }

    public function update(User $user, AdobeRecording $recording): bool
    {
        return $user->can('adobe-recordings-update') && $this->canManageCourse($user, $recording);
    }

    public function delete(User $user, AdobeRecording $recording): bool
    {
        return $user->can('adobe-recordings-delete') && $this->canManageCourse($user, $recording);
    }

    private function canManageCourse(User $user, AdobeRecording $recording): bool
    {
        $course = $recording->classSession?->courseClass?->course;

        return $course !== null && CourseOwnership::canManage($user, $course);
    }
}
