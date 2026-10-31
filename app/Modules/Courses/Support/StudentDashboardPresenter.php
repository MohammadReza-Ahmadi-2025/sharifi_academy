<?php

namespace App\Modules\Courses\Support;

use App\Modules\Courses\Enums\CourseClassOnlinePlatform;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Enums\SessionAttendanceStatus;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Courses\Models\SessionAttendance;
use Illuminate\Support\Collection;

final class StudentDashboardPresenter
{
    /**
     * @param  Collection<int, Enrollment>  $enrollments
     * @return list<array<string, mixed>>
     */
    public static function enrollments(Collection $enrollments): array
    {
        return $enrollments
            ->map(fn (Enrollment $enrollment) => self::enrollment($enrollment))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function enrollment(Enrollment $enrollment): array
    {
        $courseClass = $enrollment->courseClass;
        $course = $courseClass?->course;
        $sessions = $courseClass?->classSessions ?? collect();
        $courseClassModel = $courseClass instanceof CourseClass ? $courseClass : null;
        $includeJoin = $enrollment->isConfirmed();

        $meeting = $courseClassModel !== null
            ? CourseClassMeetingSchedule::forClass($courseClassModel)
            : null;

        return [
            'id' => $enrollment->id,
            'status' => $enrollment->status?->value ?? EnrollmentStatus::Pending->value,
            'status_label' => $enrollment->status_label,
            'confirmed_at' => $enrollment->confirmed_at?->toIso8601String(),
            'show_url' => route('student.courses.show', $enrollment),
            'course' => [
                'id' => (int) ($course?->id ?? 0),
                'title' => (string) ($course?->title ?? 'دوره'),
                'slug' => $course !== null ? $course->publicSlug() : '',
                'public_url' => $course !== null ? route('courses.show', $course) : null,
            ],
            'class_name' => (string) ($courseClass?->name ?? ''),
            'class_code' => (string) ($courseClass?->code ?? ''),
            'meeting_summary_label' => $meeting['summary_label'] ?? null,
            'meeting_days_label' => $meeting['days_label'] ?? null,
            'meeting_time_label' => $meeting['time_label'] ?? null,
            'sessions_count' => $sessions->count(),
            'upcoming_sessions_count' => $sessions
                ->filter(fn (ClassSession $session) => $session->scheduled_start_at !== null
                    && $session->scheduled_start_at->gte(now()))
                ->count(),
            'sessions' => $sessions
                ->map(fn (ClassSession $session) => self::session($session, $courseClassModel, $includeJoin))
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  Collection<int, Enrollment>  $enrollments
     * @return list<array<string, mixed>>
     */
    public static function upcomingSessions(Collection $enrollments, int $limit = 8): array
    {
        $items = collect();

        foreach ($enrollments as $enrollment) {
            if (! $enrollment->isConfirmed()) {
                continue;
            }

            $courseClass = $enrollment->courseClass;
            if (! $courseClass instanceof CourseClass) {
                continue;
            }

            $course = $courseClass->course;
            foreach ($courseClass->classSessions ?? [] as $session) {
                if ($session->scheduled_start_at === null || $session->scheduled_start_at->lt(now())) {
                    continue;
                }

                $items->push([
                    ...self::session($session, $courseClass, true),
                    'enrollment_id' => $enrollment->id,
                    'course_title' => (string) ($course?->title ?? 'دوره'),
                    'class_name' => (string) $courseClass->name,
                    'show_url' => route('student.courses.show', $enrollment),
                ]);
            }
        }

        return $items
            ->sortBy('starts_at')
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Enrollment>  $enrollments
     * @return list<array<string, mixed>>
     */
    public static function allSessions(Collection $enrollments): array
    {
        $items = collect();

        foreach ($enrollments as $enrollment) {
            if (! $enrollment->isConfirmed()) {
                continue;
            }

            $courseClass = $enrollment->courseClass;
            if (! $courseClass instanceof CourseClass) {
                continue;
            }

            $course = $courseClass->course;
            foreach ($courseClass->classSessions ?? [] as $session) {
                $startsAt = $session->scheduled_start_at;
                $items->push([
                    ...self::session($session, $courseClass, true),
                    'enrollment_id' => $enrollment->id,
                    'course_title' => (string) ($course?->title ?? 'دوره'),
                    'class_name' => (string) $courseClass->name,
                    'show_url' => route('student.courses.show', $enrollment),
                    'is_upcoming' => $startsAt !== null && $startsAt->gte(now()),
                ]);
            }
        }

        return $items
            ->sortByDesc(fn (array $row) => $row['starts_at'] ?? '')
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, SessionAttendance>  $attendances
     * @return list<array<string, mixed>>
     */
    public static function attendances(Collection $attendances): array
    {
        return $attendances
            ->map(function (SessionAttendance $row): array {
                $session = $row->classSession;
                $courseClass = $session?->courseClass;
                $course = $courseClass?->course;

                return [
                    'id' => $row->id,
                    'status' => $row->status?->value ?? SessionAttendanceStatus::Absent->value,
                    'status_label' => $row->status_label,
                    'duration_minutes' => (int) ($row->duration_minutes ?? 0),
                    'session_title' => (string) ($session?->title ?? 'جلسه'),
                    'starts_at' => $session?->scheduled_start_at?->toIso8601String(),
                    'course_title' => (string) ($course?->title ?? 'دوره'),
                    'class_name' => (string) ($courseClass?->name ?? ''),
                    'note' => (string) ($row->note ?? ''),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private static function session(
        ClassSession $session,
        ?CourseClass $courseClassModel,
        bool $includeJoin,
    ): array {
        $joinTarget = $includeJoin && $courseClassModel !== null
            ? ClassSessionJoinUrl::externalTarget($session, $courseClassModel)
            : null;

        $platform = $courseClassModel?->online_platform ?? CourseClassOnlinePlatform::AdobeConnect;
        $internalJoinUrl = $includeJoin && $joinTarget !== null && $courseClassModel !== null
            ? route('student.sessions.join', [
                'courseClass' => $courseClassModel->id,
                'classSession' => $session->id,
            ])
            : null;
        $joinLabel = $includeJoin && $courseClassModel !== null && $joinTarget !== null
            ? ClassSessionJoinUrl::joinLabel($courseClassModel)
            : null;

        $joinHint = $includeJoin
            ? self::joinUnavailableHint($session, $courseClassModel, $joinTarget)
            : ($courseClassModel !== null && ! $includeJoin
                ? 'پس از تأیید ثبت‌نام توسط آموزش، امکان ورود به کلاس فعال می‌شود.'
                : null);

        $adobeLoginHint = $includeJoin
            && $courseClassModel !== null
            && ! $courseClassModel->usesVideoLink()
            && $joinTarget !== null
            ? 'ورود Adobe: نام کاربری و رمز = همان شماره موبایل شما'
            : null;

        $recordings = ! $includeJoin || $platform === CourseClassOnlinePlatform::VideoLink
            ? collect()
            : ($session->adobeRecordings ?? collect());

        return [
            'id' => $session->id,
            'title' => (string) $session->title,
            'session_number' => (int) $session->session_number,
            'starts_at' => $session->scheduled_start_at?->toIso8601String(),
            'ends_at' => $session->scheduled_end_at?->toIso8601String(),
            'join_url' => $internalJoinUrl,
            'join_label' => $joinLabel,
            'join_hint' => $joinHint,
            'adobe_login_hint' => $adobeLoginHint,
            'adobe_connect_url' => $internalJoinUrl,
            'recordings' => $recordings
                ->map(fn ($recording) => [
                    'id' => $recording->id,
                    'name' => (string) ($recording->name ?: 'ضبط جلسه'),
                    'playback_url' => (string) $recording->playback_url,
                    'download_url' => $recording->download_url !== null
                        ? (string) $recording->download_url
                        : null,
                ])
                ->values()
                ->all(),
        ];
    }

    private static function joinUnavailableHint(
        ClassSession $session,
        ?CourseClass $courseClass,
        ?string $joinTarget,
    ): ?string {
        if ($joinTarget !== null || $courseClass === null) {
            return null;
        }

        if ($courseClass->usesVideoLink()) {
            return 'لینک ویدیو این جلسه هنوز در پنل آموزش ثبت نشده است.';
        }

        return 'اتاق Adobe Connect برای این کلاس هنوز وصل نشده است. پس از تنظیم توسط آموزش، دکمه ورود اینجا ظاهر می‌شود.';
    }
}
