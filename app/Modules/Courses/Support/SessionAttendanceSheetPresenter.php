<?php

namespace App\Modules\Courses\Support;

use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Enums\SessionAttendanceStatus;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\SessionAttendance;
final class SessionAttendanceSheetPresenter
{
    /**
     * @return list<array{user_id: int, name: string, mobile: string|null, status: string, status_label: string, note: string}>
     */
    public static function rowsForSession(ClassSession $classSession): array
    {
        $courseClass = $classSession->courseClass;
        if ($courseClass === null) {
            return [];
        }

        $enrollments = $courseClass->enrollments()
            ->where('status', EnrollmentStatus::Confirmed)
            ->with('user')
            ->get();

        $existing = SessionAttendance::query()
            ->where('class_session_id', $classSession->id)
            ->get()
            ->keyBy('user_id');

        return $enrollments
            ->map(function ($enrollment) use ($existing): array {
                $user = $enrollment->user;
                $attendance = $existing->get($user->id);
                $status = $attendance?->status ?? SessionAttendanceStatus::Absent;

                return [
                    'user_id' => $user->id,
                    'name' => trim(($user->name ?? '').' '.($user->family ?? '')),
                    'mobile' => $user->mobile,
                    'status' => $status->value,
                    'status_label' => $status->label(),
                    'note' => (string) ($attendance?->note ?? ''),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array{id: int, title: string, session_number: int, starts_at: string|null, course_title: string|null, class_title: string|null}
     */
    public static function sessionMeta(ClassSession $classSession): array
    {
        $classSession->loadMissing(['courseClass.course']);
        $course = $classSession->courseClass?->course;

        return [
            'id' => $classSession->id,
            'title' => (string) $classSession->title,
            'session_number' => (int) $classSession->session_number,
            'starts_at' => $classSession->scheduled_start_at?->toIso8601String(),
            'course_title' => $course?->title,
            'class_title' => $classSession->courseClass?->name,
        ];
    }
}
