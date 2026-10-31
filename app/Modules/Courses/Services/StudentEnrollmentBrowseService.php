<?php

namespace App\Modules\Courses\Services;

use App\Models\User;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Enums\SessionAttendanceStatus;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Courses\Models\SessionAttendance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class StudentEnrollmentBrowseService
{
    /**
     * @return Collection<int, Enrollment>
     */
    public function confirmedEnrollmentsFor(User $user): Collection
    {
        return $this->enrollmentsQuery($user)
            ->confirmed()
            ->get();
    }

    /**
     * همه ثبت‌نام‌های دانشجو (قطعی، در انتظار، لغو).
     *
     * @return Collection<int, Enrollment>
     */
    public function enrollmentsFor(User $user): Collection
    {
        return $this->enrollmentsQuery($user)->get();
    }

    public function enrollmentFor(User $user, Enrollment $enrollment): Enrollment
    {
        abort_unless((int) $enrollment->user_id === (int) $user->id, 404);

        $enrollment->load([
            'courseClass.course',
            'courseClass.classLevelAdobeRooms',
            'courseClass.classSessions' => fn ($query) => $query
                ->with([
                    'adobeRooms',
                    'adobeRecordings' => $this->publicRecordingsConstraint(),
                ])
                ->orderBy('scheduled_start_at')
                ->orderBy('session_number')
                ->orderBy('id'),
        ]);

        return $enrollment;
    }

    /**
     * @return Collection<int, SessionAttendance>
     */
    public function attendancesFor(User $user): Collection
    {
        $classIds = Enrollment::query()
            ->confirmed()
            ->where('user_id', $user->id)
            ->pluck('course_class_id');

        if ($classIds->isEmpty()) {
            return new Collection;
        }

        return SessionAttendance::query()
            ->where('user_id', $user->id)
            ->whereHas('classSession', fn ($query) => $query->whereIn('course_class_id', $classIds))
            ->with(['classSession.courseClass.course'])
            ->join('class_sessions', 'class_sessions.id', '=', 'session_attendances.class_session_id')
            ->orderByDesc('class_sessions.scheduled_start_at')
            ->orderByDesc('session_attendances.id')
            ->select('session_attendances.*')
            ->get();
    }

    /**
     * @return array{
     *     courses_confirmed: int,
     *     courses_pending: int,
     *     sessions_upcoming: int,
     *     attendance_rate: int|null,
     *     attendance_present: int,
     *     attendance_absent: int
     * }
     */
    public function statsFor(User $user): array
    {
        $confirmedCount = Enrollment::query()->confirmed()->where('user_id', $user->id)->count();
        $pendingCount = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('status', EnrollmentStatus::Pending)
            ->count();

        $confirmedRows = $this->confirmedEnrollmentsFor($user);
        $upcoming = 0;
        foreach ($confirmedRows as $enrollment) {
            foreach ($enrollment->courseClass?->classSessions ?? [] as $session) {
                if ($session->scheduled_start_at !== null && $session->scheduled_start_at->gte(now())) {
                    $upcoming++;
                }
            }
        }

        $attendances = $this->attendancesFor($user);
        $present = $attendances
            ->filter(fn (SessionAttendance $row) => $row->status === SessionAttendanceStatus::Present)
            ->count();
        $absent = $attendances
            ->filter(fn (SessionAttendance $row) => $row->status === SessionAttendanceStatus::Absent)
            ->count();
        $scored = $present + $absent;

        return [
            'courses_confirmed' => $confirmedCount,
            'courses_pending' => $pendingCount,
            'sessions_upcoming' => $upcoming,
            'attendance_rate' => $scored > 0 ? (int) round(($present / $scored) * 100) : null,
            'attendance_present' => $present,
            'attendance_absent' => $absent,
        ];
    }

    /**
     * @return Builder<Enrollment>
     */
    private function enrollmentsQuery(User $user): Builder
    {
        return Enrollment::query()
            ->where('user_id', $user->id)
            ->with([
                'courseClass.course',
                'courseClass.classLevelAdobeRooms',
                'courseClass.classSessions' => fn ($query) => $query
                    ->with([
                        'adobeRooms',
                        'adobeRecordings' => $this->publicRecordingsConstraint(),
                    ])
                    ->orderBy('scheduled_start_at')
                    ->orderBy('session_number')
                    ->orderBy('id'),
            ])
            ->orderByRaw("CASE status WHEN 'confirmed' THEN 0 WHEN 'pending' THEN 1 ELSE 2 END")
            ->orderByDesc('confirmed_at')
            ->orderByDesc('id');
    }

    private function publicRecordingsConstraint(): \Closure
    {
        return fn ($query) => $query
            ->where('is_public', true)
            ->whereNotNull('playback_url')
            ->where('is_passcode_protected', false);
    }
}
