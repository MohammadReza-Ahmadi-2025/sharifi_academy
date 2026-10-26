<?php

namespace App\Modules\AdobeConnect\Policies;

use App\Models\User;
use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\Courses\Support\CourseOwnership;

class AdobeRoomPolicy
{
    public function view(User $user, AdobeRoom $room): bool
    {
        return $user->can('adobe-rooms-index') && $this->canManageCourse($user, $room);
    }

    public function update(User $user, AdobeRoom $room): bool
    {
        return $user->can('adobe-rooms-update') && $this->canManageCourse($user, $room);
    }

    public function delete(User $user, AdobeRoom $room): bool
    {
        return $user->can('adobe-rooms-delete') && $this->canManageCourse($user, $room);
    }

    private function canManageCourse(User $user, AdobeRoom $room): bool
    {
        $course = $room->courseClass?->course;

        return $course !== null && CourseOwnership::canManage($user, $course);
    }
}
