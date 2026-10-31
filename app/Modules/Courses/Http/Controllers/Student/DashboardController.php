<?php

namespace App\Modules\Courses\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Courses\Services\StudentEnrollmentBrowseService;
use App\Modules\Courses\Support\StudentDashboardPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, StudentEnrollmentBrowseService $enrollments): Response
    {
        $user = $request->user();

        abort_unless($user !== null, 403);

        $this->authorize('viewAny', Enrollment::class);

        $confirmed = $enrollments->confirmedEnrollmentsFor($user);
        $all = $enrollments->enrollmentsFor($user);

        return Inertia::render('Student/Dashboard', [
            'stats' => $enrollments->statsFor($user),
            'upcomingSessions' => StudentDashboardPresenter::upcomingSessions($confirmed),
            'enrollments' => StudentDashboardPresenter::enrollments($all->take(6)),
        ]);
    }
}
