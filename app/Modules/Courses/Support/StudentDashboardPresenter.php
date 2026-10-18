<?php

namespace App\Modules\Courses\Support;

use App\Modules\AdobeConnect\Support\AdobeConnectJoinUrl;
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
     *     sessions: list<array{
     *         id: int,
     *         title: string,
     *         starts_at: string|null,
     *         adobe_connect_url: string|null
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
                    'sessions' => $sessions
                        ->map(function (ClassSession $session) use ($courseClassModel): array {
                            $joinTarget = $courseClassModel !== null
                                ? AdobeConnectJoinUrl::forSession($session, $courseClassModel)
                                : null;

                            return [
                                'id' => $session->id,
                                'title' => (string) $session->title,
                                'starts_at' => $session->scheduled_start_at?->toIso8601String(),
                                'adobe_connect_url' => $joinTarget !== null && $courseClassModel !== null
                                    ? route('student.sessions.adobe', [
                                        'courseClass' => $courseClassModel->id,
                                        'classSession' => $session->id,
                                    ])
                                    : null,
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }
}
