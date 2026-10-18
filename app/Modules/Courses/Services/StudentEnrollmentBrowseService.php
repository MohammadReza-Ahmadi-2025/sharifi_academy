<?php

namespace App\Modules\Courses\Services;

use App\Models\User;
use App\Modules\Courses\Models\Enrollment;
use Illuminate\Database\Eloquent\Collection;

class StudentEnrollmentBrowseService
{
    /**
     * @return Collection<int, Enrollment>
     */
    public function confirmedEnrollmentsFor(User $user): Collection
    {
        return Enrollment::query()
            ->confirmed()
            ->where('user_id', $user->id)
            ->with([
                'courseClass.course',
                'courseClass.classLevelAdobeRooms',
                'courseClass.classSessions.adobeRooms',
            ])
            ->orderByDesc('confirmed_at')
            ->orderByDesc('id')
            ->get();
    }
}
