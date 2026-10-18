<?php

namespace App\Modules\Admin\Services;

use App\Models\User;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Profile\Enums\ResumeModerationStatus;

final class InstructorModerationQueue
{
    /**
     * @return array{courses_pending: int, resumes_pending: int, total: int}
     */
    public static function counts(): array
    {
        $coursesPending = Course::query()
            ->where('status', CourseStatus::PendingReview)
            ->count();

        $resumesPending = User::query()
            ->role('instructor')
            ->where('status', 1)
            ->whereHas('profile', fn ($q) => $q->where('resume_moderation_status', ResumeModerationStatus::Pending->value))
            ->count();

        return [
            'courses_pending' => $coursesPending,
            'resumes_pending' => $resumesPending,
            'total' => $coursesPending + $resumesPending,
        ];
    }
}
