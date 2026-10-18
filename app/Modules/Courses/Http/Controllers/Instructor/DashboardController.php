<?php

namespace App\Modules\Courses\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Support\CourseOwnership;
use App\Modules\Courses\Support\CoursePresenter;
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

        $stats = [
            'courses_total' => $courseIds->count(),
            'courses_published' => (clone $ownedQuery)->where('status', CourseStatus::Published)->count(),
            'courses_draft' => (clone $ownedQuery)->where('status', CourseStatus::Draft)->count(),
            'classes_total' => $courseIds->isEmpty()
                ? 0
                : CourseClass::query()->whereIn('course_id', $courseIds)->count(),
        ];

        return Inertia::render('Instructor/Dashboard', [
            'courses' => $courses,
            'stats' => $stats,
            'canCreateCourse' => $user->can('create', Course::class),
        ]);
    }
}
