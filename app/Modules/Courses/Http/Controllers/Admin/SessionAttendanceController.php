<?php

namespace App\Modules\Courses\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Enums\SessionAttendanceSource;
use App\Modules\Courses\Enums\SessionAttendanceStatus;
use App\Modules\Courses\Http\Requests\Admin\SyncSessionAttendanceRequest;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\SessionAttendance;
use App\Modules\Courses\Support\AdminSessionAttendanceOptions;
use App\Modules\Courses\Support\SessionAttendanceSheetPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SessionAttendanceController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('session-attendances-index'), 403);

        $search = trim((string) $request->query('search', ''));
        $perPage = in_array((int) $request->query('per_page', 25), [25, 50, 100], true)
            ? (int) $request->query('per_page', 25)
            : 25;

        $query = ClassSession::query()
            ->with(['courseClass.course'])
            ->withCount('attendances')
            ->orderByDesc('scheduled_start_at');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhereHas('courseClass.course', fn ($q) => $q->where('title', 'like', "%{$search}%"));
            });
        }

        $sessions = $query->paginate($perPage)->withQueryString()
            ->through(function (ClassSession $session) {
                $course = $session->courseClass?->course;

                return [
                    'id' => $session->id,
                    'title' => $session->title,
                    'session_number' => $session->session_number,
                    'starts_at' => $session->scheduled_start_at?->toIso8601String(),
                    'course_title' => $course?->title,
                    'class_title' => $session->courseClass?->title,
                    'attendances_count' => $session->attendances_count,
                ];
            });

        return Inertia::render('Admin/SessionAttendances/Index', [
            'sessions' => $sessions,
            'filters' => ['search' => $search, 'per_page' => $perPage],
            'abilities' => [
                'update' => $request->user()?->can('session-attendances-update') ?? false,
            ],
        ]);
    }

    public function show(Request $request, ClassSession $classSession): Response
    {
        abort_unless($request->user()?->can('session-attendances-index'), 403);

        $classSession->load(['courseClass.course']);

        $courseClass = $classSession->courseClass;
        abort_if($courseClass === null, 404);

        return Inertia::render('Admin/SessionAttendances/Show', [
            'session' => SessionAttendanceSheetPresenter::sessionMeta($classSession),
            'rows' => SessionAttendanceSheetPresenter::rowsForSession($classSession),
            'statusOptions' => AdminSessionAttendanceOptions::statuses(),
            'abilities' => [
                'update' => $request->user()?->can('session-attendances-update') ?? false,
            ],
        ]);
    }

    public function sync(SyncSessionAttendanceRequest $request, ClassSession $classSession): RedirectResponse
    {
        foreach ($request->validated('attendances') as $row) {
            SessionAttendance::query()->updateOrCreate(
                [
                    'class_session_id' => $classSession->id,
                    'user_id' => $row['user_id'],
                ],
                [
                    'status' => $row['status'],
                    'note' => $row['note'] ?? null,
                    'source' => SessionAttendanceSource::Manual,
                ],
            );
        }

        return back()->with('success', 'حضور و غیاب ذخیره شد.');
    }
}
