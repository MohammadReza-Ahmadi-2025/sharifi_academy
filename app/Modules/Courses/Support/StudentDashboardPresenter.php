<?php

namespace App\Modules\Courses\Support;

use App\Modules\Courses\Enums\CourseClassOnlinePlatform;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use Illuminate\Support\Collection;

final class StudentDashboardPresenter
{
    /**
     * @param  Collection<int, Enrollment>  $enrollments
     * @return list<array{
     *     id: int,
     *     course: array{id: int, title: string, slug: string},
     *     class_name: string,
     *     sessions: list<array{
     *         id: int,
     *         title: string,
     *         starts_at: string|null,
     *         join_url: string|null,
     *         join_label: string|null,
     *         join_hint: string|null,
     *         adobe_connect_url: string|null,
     *         recordings: list<array{id: int, name: string, playback_url: string, download_url: string|null}>
     *     }>
     * }>
     */
    public static function enrollments(Collection $enrollments): array
    {
        return $enrollments
            ->map(function (Enrollment $enrollment): array {
                $courseClass = $enrollment->courseClass;
                $course = $courseClass?->course;

                $sessions = $courseClass?->classSessions ?? collect();
                $courseClassModel = $courseClass instanceof CourseClass ? $courseClass : null;

                return [
                    'id' => $enrollment->id,
                    'course' => [
                        'id' => (int) ($course?->id ?? 0),
                        'title' => (string) ($course?->title ?? 'دوره'),
                        'slug' => $course !== null ? $course->publicSlug() : '',
                    ],
                    'class_name' => (string) ($courseClass?->name ?? ''),
                    'sessions' => $sessions
                        ->map(function (ClassSession $session) use ($courseClassModel): array {
                            $joinTarget = $courseClassModel !== null
                                ? ClassSessionJoinUrl::externalTarget($session, $courseClassModel)
                                : null;

                            $platform = $courseClassModel?->online_platform ?? CourseClassOnlinePlatform::AdobeConnect;
                            $internalJoinUrl = $joinTarget !== null && $courseClassModel !== null
                                ? route('student.sessions.join', [
                                    'courseClass' => $courseClassModel->id,
                                    'classSession' => $session->id,
                                ])
                                : null;
                            $joinLabel = $courseClassModel !== null && $joinTarget !== null
                                ? ClassSessionJoinUrl::joinLabel($courseClassModel)
                                : null;

                            $joinHint = self::joinUnavailableHint($session, $courseClassModel, $joinTarget);
                            $adobeLoginHint = $courseClassModel !== null
                                && ! $courseClassModel->usesVideoLink()
                                && $joinTarget !== null
                                ? 'ورود Adobe: نام کاربری و رمز = همان شماره موبایل شما'
                                : null;

                            $recordings = $platform === CourseClassOnlinePlatform::VideoLink
                                ? collect()
                                : ($session->adobeRecordings ?? collect());

                            return [
                                'id' => $session->id,
                                'title' => (string) $session->title,
                                'starts_at' => $session->scheduled_start_at?->toIso8601String(),
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
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
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
