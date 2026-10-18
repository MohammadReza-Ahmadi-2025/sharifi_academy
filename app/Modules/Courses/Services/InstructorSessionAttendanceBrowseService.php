<?php

namespace App\Modules\Courses\Services;

use App\Models\User;
use App\Modules\Courses\Models\ClassSession;
use Illuminate\Database\Eloquent\Collection;

class InstructorSessionAttendanceBrowseService
{
    /**
     * @return Collection<int, ClassSession>
     */
    public function sessionsForInstructor(User $user): Collection
    {
        return ClassSession::query()
            ->with(['courseClass.course'])
            ->withCount('attendances')
            ->whereHas(
                'courseClass.instructors',
                fn ($instructorQuery) => $instructorQuery->where('users.id', $user->id),
            )
            ->orderByDesc('scheduled_start_at')
            ->orderByDesc('id')
            ->limit(150)
            ->get();
    }
}
