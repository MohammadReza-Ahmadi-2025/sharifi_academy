<?php

namespace App\Modules\Courses\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\AdobeConnect\Services\SyncAdobeSessionAttendanceService;
use App\Modules\AdobeConnect\Support\AdobeConnectSessionSco;
use App\Modules\Courses\Enums\SessionAttendanceSource;
use App\Modules\Courses\Http\Requests\Admin\SyncAdobeSessionAttendanceRequest;
use App\Modules\Courses\Http\Requests\Admin\SyncSessionAttendanceRequest;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\SessionAttendance;
use App\Modules\Courses\Support\AdminSessionAttendanceOptions;
use App\Modules\Courses\Support\SessionAttendanceDashboardPresenter;
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

        $paginator = $query->paginate($perPage)->withQueryString();
        $rows = SessionAttendanceDashboardPresenter::enrichSessionRows(
            collect($paginator->items()),
        );

        return Inertia::render('Admin/SessionAttendances/Index', [
            'summary' => SessionAttendanceDashboardPresenter::summary(),
            'sessions' => [
                'data' => $rows,
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'filters' => ['search' => $search, 'per_page' => $perPage],
            'abilities' => [
                'update' => $request->user()?->can('session-attendances-update') ?? false,
            ],
        ]);
    }

    public function show(Request $request, ClassSession $classSession): Response
    {
        abort_unless($request->user()?->can('session-attendances-index'), 403);

        $classSession->load(['courseClass.course', 'adobeRooms', 'courseClass.classLevelAdobeRooms']);

        $courseClass = $classSession->courseClass;
        abort_if($courseClass === null, 404);

        $canUpdate = $request->user()?->can('session-attendances-update') ?? false;

        return Inertia::render('Admin/SessionAttendances/Show', [
            'session' => SessionAttendanceSheetPresenter::sessionMeta($classSession),
            'rows' => SessionAttendanceSheetPresenter::rowsForSession($classSession),
            'statusOptions' => AdminSessionAttendanceOptions::statuses(),
            'can_sync_adobe' => $canUpdate && AdobeConnectSessionSco::meetingScoId($classSession) !== null,
            'abilities' => [
                'update' => $canUpdate,
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

    public function syncAdobe(
        SyncAdobeSessionAttendanceRequest $request,
        ClassSession $classSession,
        SyncAdobeSessionAttendanceService $adobeAttendance,
    ): RedirectResponse {
        $stats = $adobeAttendance->sync(
            $classSession,
            $request->boolean('mark_absent', true),
        );

        return back()->with(
            'success',
            sprintf(
                'حضور از Adobe همگام شد: %d حاضر، %d غایب، %d بدون تغییر (sco=%s).',
                $stats['present'],
                $stats['absent'],
                $stats['skipped'],
                $stats['sco_id'],
            ),
        );
    }
}
