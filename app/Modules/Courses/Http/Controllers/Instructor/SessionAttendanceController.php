<?php

namespace App\Modules\Courses\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Services\InstructorSessionAttendanceBrowseService;
use App\Modules\Courses\Support\SessionAttendanceSheetPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SessionAttendanceController extends Controller
{
    public function index(Request $request, InstructorSessionAttendanceBrowseService $browse): Response
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $sessions = $browse->sessionsForInstructor($user)
            ->map(function (ClassSession $session): array {
                $course = $session->courseClass?->course;

                return [
                    'id' => $session->id,
                    'title' => $session->title,
                    'session_number' => $session->session_number,
                    'starts_at' => $session->scheduled_start_at?->toIso8601String(),
                    'course_title' => $course?->title,
                    'class_title' => $session->courseClass?->name,
                    'attendances_count' => $session->attendances_count,
                    'show_url' => route('instructor.session-attendances.show', $session),
                ];
            })
            ->values()
            ->all();

        return Inertia::render('Instructor/SessionAttendances/Index', [
            'sessions' => $sessions,
        ]);
    }

    public function show(Request $request, ClassSession $classSession): Response
    {
        $this->authorize('viewAttendance', $classSession);

        return Inertia::render('Instructor/SessionAttendances/Show', [
            'session' => SessionAttendanceSheetPresenter::sessionMeta($classSession),
            'rows' => SessionAttendanceSheetPresenter::rowsForSession($classSession),
        ]);
    }
}
