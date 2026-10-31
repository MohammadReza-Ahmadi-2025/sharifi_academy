<?php

namespace App\Modules\Courses\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Courses\Services\StudentEnrollmentBrowseService;
use App\Modules\Courses\Support\StudentDashboardPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseEnrollmentController extends Controller
{
    public function index(Request $request, StudentEnrollmentBrowseService $enrollments): Response
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $this->authorize('viewAny', Enrollment::class);

        $rows = $enrollments->enrollmentsFor($user);

        return Inertia::render('Student/Courses/Index', [
            'enrollments' => StudentDashboardPresenter::enrollments($rows),
        ]);
    }

    public function show(
        Request $request,
        Enrollment $enrollment,
        StudentEnrollmentBrowseService $enrollments,
    ): Response {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $this->authorize('view', $enrollment);

        $row = $enrollments->enrollmentFor($user, $enrollment);

        return Inertia::render('Student/Courses/Show', [
            'enrollment' => StudentDashboardPresenter::enrollment($row),
        ]);
    }
}
