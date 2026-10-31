<?php

namespace App\Modules\Courses\Support;

use App\Models\User;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Enums\SessionAttendanceStatus;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\SessionAttendance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class SessionAttendanceDashboardPresenter
{
    /**
     * @return array{
     *     sessions_total: int,
     *     sessions_with_records: int,
     *     present_count: int,
     *     absent_count: int,
     *     excused_count: int,
     *     late_count: int,
     *     attendance_rate: float|null,
     *     avg_duration_minutes: int,
     *     low_attendance: list<array{id: int, title: string, course_title: string|null, rate: float, present: int, enrolled: int}>
     * }
     */
    public static function summary(?User $instructor = null): array
    {
        $sessionIds = self::sessionQuery($instructor)->pluck('id');

        if ($sessionIds->isEmpty()) {
            return [
                'sessions_total' => 0,
                'sessions_with_records' => 0,
                'present_count' => 0,
                'absent_count' => 0,
                'excused_count' => 0,
                'late_count' => 0,
                'attendance_rate' => null,
                'avg_duration_minutes' => 0,
                'low_attendance' => [],
            ];
        }

        $counts = SessionAttendance::query()
            ->whereIn('class_session_id', $sessionIds)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $present = (int) ($counts[SessionAttendanceStatus::Present->value] ?? 0);
        $absent = (int) ($counts[SessionAttendanceStatus::Absent->value] ?? 0);
        $excused = (int) ($counts[SessionAttendanceStatus::Excused->value] ?? 0);
        $late = (int) ($counts[SessionAttendanceStatus::Late->value] ?? 0);
        $tracked = $present + $absent + $late;
        $sessionsWithRecords = (int) SessionAttendance::query()
            ->whereIn('class_session_id', $sessionIds)
            ->toBase()
            ->distinct()
            ->count('class_session_id');

        $avgDuration = (int) round((float) SessionAttendance::query()
            ->whereIn('class_session_id', $sessionIds)
            ->where('status', SessionAttendanceStatus::Present)
            ->avg('duration_minutes'));

        return [
            'sessions_total' => $sessionIds->count(),
            'sessions_with_records' => $sessionsWithRecords,
            'present_count' => $present,
            'absent_count' => $absent,
            'excused_count' => $excused,
            'late_count' => $late,
            'attendance_rate' => $tracked > 0
                ? round((($present + $late) / $tracked) * 100, 1)
                : null,
            'avg_duration_minutes' => $avgDuration,
            'low_attendance' => self::lowAttendanceSessions($instructor, 5),
        ];
    }

    /**
     * @return list<array{id: int, title: string, course_title: string|null, class_title: string|null, present_count: int, absent_count: int, enrolled_count: int, attendance_rate: float|null, starts_at: string|null}>
     */
    public static function enrichSessionRows(Collection $sessions): array
    {
        if ($sessions->isEmpty()) {
            return [];
        }

        $sessionIds = $sessions->pluck('id')->all();

        $attendanceGroups = SessionAttendance::query()
            ->whereIn('class_session_id', $sessionIds)
            ->get()
            ->groupBy('class_session_id');

        $enrolledCounts = [];
        foreach ($sessions as $session) {
            $courseClass = $session->courseClass;
            $enrolledCounts[$session->id] = $courseClass === null
                ? 0
                : $courseClass->enrollments()
                    ->where('status', EnrollmentStatus::Confirmed)
                    ->count();
        }

        return $sessions->map(function (ClassSession $session) use ($attendanceGroups, $enrolledCounts): array {
            $rows = $attendanceGroups->get($session->id, collect());
            $present = $rows->where('status', SessionAttendanceStatus::Present)->count()
                + $rows->where('status', SessionAttendanceStatus::Late)->count();
            $absent = $rows->where('status', SessionAttendanceStatus::Absent)->count();
            $enrolled = $enrolledCounts[$session->id] ?? 0;
            $tracked = $present + $absent;
            $course = $session->courseClass?->course;

            return [
                'id' => $session->id,
                'title' => (string) $session->title,
                'session_number' => (int) $session->session_number,
                'starts_at' => $session->scheduled_start_at?->toIso8601String(),
                'course_title' => $course?->title,
                'class_title' => $session->courseClass?->name,
                'attendances_count' => $rows->count(),
                'present_count' => $present,
                'absent_count' => $absent,
                'enrolled_count' => $enrolled,
                'attendance_rate' => $tracked > 0
                    ? round(($present / $tracked) * 100, 1)
                    : null,
            ];
        })->values()->all();
    }

    /**
     * @return list<array{id: int, title: string, course_title: string|null, rate: float, present: int, enrolled: int}>
     */
    private static function lowAttendanceSessions(?User $instructor, int $limit): array
    {
        $sessions = self::sessionQuery($instructor)
            ->with(['courseClass.course'])
            ->withCount([
                'attendances as present_count' => fn ($q) => $q->whereIn('status', [
                    SessionAttendanceStatus::Present->value,
                    SessionAttendanceStatus::Late->value,
                ]),
                'attendances as absent_count' => fn ($q) => $q->where('status', SessionAttendanceStatus::Absent->value),
            ])
            ->orderByDesc('scheduled_start_at')
            ->limit(40)
            ->get();

        return $sessions
            ->map(function (ClassSession $session) {
                $present = (int) ($session->present_count ?? 0);
                $absent = (int) ($session->absent_count ?? 0);
                $tracked = $present + $absent;
                if ($tracked < 1) {
                    return null;
                }

                $rate = round(($present / $tracked) * 100, 1);
                if ($rate >= 70) {
                    return null;
                }

                return [
                    'id' => $session->id,
                    'title' => (string) $session->title,
                    'course_title' => $session->courseClass?->course?->title,
                    'rate' => $rate,
                    'present' => $present,
                    'enrolled' => $tracked,
                ];
            })
            ->filter()
            ->sortBy('rate')
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @return Builder<ClassSession>
     */
    private static function sessionQuery(?User $instructor): Builder
    {
        $query = ClassSession::query();

        if ($instructor !== null) {
            $query->whereHas(
                'courseClass.instructors',
                fn ($instructorQuery) => $instructorQuery->where('users.id', $instructor->id),
            );
        }

        return $query;
    }
}
