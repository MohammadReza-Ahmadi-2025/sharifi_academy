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
                'courseClass.classSessions.adobeRecordings' => fn ($query) => $query
                    ->where('is_public', true)
                    ->whereNotNull('playback_url')
                    ->where('is_passcode_protected', false),
            ])
            ->orderByDesc('confirmed_at')
            ->orderByDesc('id')
            ->get();
    }
}
