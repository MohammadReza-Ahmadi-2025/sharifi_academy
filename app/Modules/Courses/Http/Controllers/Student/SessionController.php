<?php

namespace App\Modules\Courses\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Courses\Services\StudentEnrollmentBrowseService;
use App\Modules\Courses\Support\StudentDashboardPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SessionController extends Controller
{
    public function index(Request $request, StudentEnrollmentBrowseService $enrollments): Response
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $this->authorize('viewAny', Enrollment::class);

        $confirmed = $enrollments->confirmedEnrollmentsFor($user);

        return Inertia::render('Student/Sessions/Index', [
            'sessions' => StudentDashboardPresenter::allSessions($confirmed),
        ]);
    }
}
