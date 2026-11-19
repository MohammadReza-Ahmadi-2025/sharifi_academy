<?php

namespace App\Modules\Courses\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Support\ClassSessionJoinUrl;
use App\Modules\Courses\Support\CourseOwnership;
use App\Modules\Courses\Support\CoursePresenter;
use App\Modules\Courses\Support\SessionAttendanceDashboardPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $ownedQuery = CourseOwnership::scopeForManager(Course::query(), $user);
        $courseIds = (clone $ownedQuery)->pluck('id');

        $courses = (clone $ownedQuery)
            ->withCount('courseClasses')
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get()
            ->map(fn (Course $course) => [
                ...CoursePresenter::forList($course),
                'edit_url' => route('instructor.courses.edit', $course),
                'classes_url' => route('instructor.courses.classes.index', $course),
            ]);

        $attendance = SessionAttendanceDashboardPresenter::summary($user);

        $stats = [
            'courses_total' => $courseIds->count(),
            'courses_published' => (clone $ownedQuery)->where('status', CourseStatus::Published)->count(),
            'courses_draft' => (clone $ownedQuery)->where('status', CourseStatus::Draft)->count(),
            'classes_total' => $courseIds->isEmpty()
                ? 0
                : CourseClass::query()->whereIn('course_id', $courseIds)->count(),
            'attendance_rate' => $attendance['attendance_rate'],
            'attendance_present' => $attendance['present_count'],
            'attendance_absent' => $attendance['absent_count'],
        ];

        $upcomingSessions = ClassSession::query()
            ->with(['courseClass.course', 'courseClass.classLevelAdobeRooms', 'adobeRooms'])
            ->whereHas(
                'courseClass.instructors',
                fn ($instructorQuery) => $instructorQuery->where('users.id', $user->id),
            )
            ->where(function ($query): void {
                $query->whereNull('scheduled_start_at')
                    ->orWhere('scheduled_start_at', '>=', now()->subHours(2));
            })
            ->orderBy('scheduled_start_at')
            ->orderBy('id')
            ->limit(8)
            ->get()
            ->map(function (ClassSession $session) {
                $courseClass = $session->courseClass;
                $joinConfigured = $courseClass !== null
                    && ClassSessionJoinUrl::externalTarget($session, $courseClass) !== null;

                return [
                    'id' => $session->id,
                    'title' => filled($session->title) ? (string) $session->title : 'جلسه '.$session->session_number,
                    'starts_at' => $session->scheduled_start_at?->toIso8601String(),
                    'course_title' => $courseClass?->course?->title,
                    'class_name' => $courseClass?->name,
                    'join_url' => $joinConfigured && $courseClass !== null
                        ? route('instructor.sessions.join', [
                            'courseClass' => $courseClass->id,
                            'classSession' => $session->id,
                        ], absolute: false)
                        : null,
                    'join_label' => $courseClass !== null
                        ? ClassSessionJoinUrl::joinLabel($courseClass)
                        : null,
                    'join_hint' => $joinConfigured
                        ? null
                        : 'اتاق Adobe یا لینک ویدیو برای این جلسه تنظیم نشده است.',
                ];
            })
            ->values()
            ->all();

        return Inertia::render('Instructor/Dashboard', [
            'courses' => $courses,
            'upcomingSessions' => $upcomingSessions,
            'stats' => $stats,
            'canCreateCourse' => $user->can('create', Course::class),
        ]);
    }
}
