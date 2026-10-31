<?php

namespace App\Modules\Courses\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Modules\AdobeConnect\Services\SyncAdobeSessionAttendanceService;
use App\Modules\AdobeConnect\Support\AdobeConnectSessionSco;
use App\Modules\Courses\Http\Requests\Admin\SyncAdobeSessionAttendanceRequest;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Services\InstructorSessionAttendanceBrowseService;
use App\Modules\Courses\Support\ClassSessionJoinUrl;
use App\Modules\Courses\Support\SessionAttendanceDashboardPresenter;
use App\Modules\Courses\Support\SessionAttendanceSheetPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SessionAttendanceController extends Controller
{
    public function index(Request $request, InstructorSessionAttendanceBrowseService $browse): Response
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $sessions = $browse->sessionsForInstructor($user);
        $rows = collect(SessionAttendanceDashboardPresenter::enrichSessionRows($sessions))
            ->map(function (array $row) use ($sessions): array {
                /** @var ClassSession|null $session */
                $session = $sessions->firstWhere('id', $row['id']);
                $courseClass = $session?->courseClass;
                $joinConfigured = $session !== null && $courseClass !== null
                    && ClassSessionJoinUrl::externalTarget($session, $courseClass) !== null;

                return [
                    ...$row,
                    'show_url' => route('instructor.session-attendances.show', $row['id']),
                    'join_url' => $joinConfigured && $courseClass !== null
                        ? route('instructor.sessions.join', [
                            'courseClass' => $courseClass->id,
                            'classSession' => $row['id'],
                        ], absolute: false)
                        : null,
                    'join_label' => $courseClass !== null
                        ? ClassSessionJoinUrl::joinLabel($courseClass)
                        : null,
                ];
            })
            ->values()
            ->all();

        return Inertia::render('Instructor/SessionAttendances/Index', [
            'summary' => SessionAttendanceDashboardPresenter::summary($user),
            'sessions' => $rows,
        ]);
    }

    public function show(Request $request, ClassSession $classSession): Response
    {
        $this->authorize('viewAttendance', $classSession);

        $classSession->loadMissing(['courseClass.classLevelAdobeRooms', 'adobeRooms', 'courseClass.course']);
        $courseClass = $classSession->courseClass;
        $joinConfigured = $courseClass !== null
            && ClassSessionJoinUrl::externalTarget($classSession, $courseClass) !== null;

        $meta = SessionAttendanceSheetPresenter::sessionMeta($classSession);
        $meta['join_url'] = $joinConfigured && $courseClass !== null
            ? route('instructor.sessions.join', [
                'courseClass' => $courseClass->id,
                'classSession' => $classSession->id,
            ], absolute: false)
            : null;
        $meta['join_label'] = $courseClass !== null
            ? ClassSessionJoinUrl::joinLabel($courseClass)
            : null;

        return Inertia::render('Instructor/SessionAttendances/Show', [
            'session' => $meta,
            'rows' => SessionAttendanceSheetPresenter::rowsForSession($classSession),
            'can_sync_adobe' => AdobeConnectSessionSco::meetingScoId($classSession) !== null,
        ]);
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
                'حضور از Adobe همگام شد: %d حاضر، %d غایب، %d بدون تغییر.',
                $stats['present'],
                $stats['absent'],
                $stats['skipped'],
            ),
        );
    }
}
