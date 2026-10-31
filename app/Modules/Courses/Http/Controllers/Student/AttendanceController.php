<?php

namespace App\Modules\Courses\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Courses\Services\StudentEnrollmentBrowseService;
use App\Modules\Courses\Support\StudentDashboardPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    public function index(Request $request, StudentEnrollmentBrowseService $enrollments): Response
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $this->authorize('viewAny', Enrollment::class);

        $stats = $enrollments->statsFor($user);
        $rows = $enrollments->attendancesFor($user);

        return Inertia::render('Student/Attendance/Index', [
            'summary' => [
                'attendance_rate' => $stats['attendance_rate'],
                'present_count' => $stats['attendance_present'],
                'absent_count' => $stats['attendance_absent'],
                'records_total' => $rows->count(),
            ],
            'attendances' => StudentDashboardPresenter::attendances($rows),
        ]);
    }
}
