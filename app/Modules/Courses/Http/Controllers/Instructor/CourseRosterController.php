<?php

namespace App\Modules\Courses\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\Enrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseRosterController extends Controller
{
    public function __invoke(Request $request, Course $course): Response
    {
        $this->authorize('view', $course);

        $classIds = $course->courseClasses()->pluck('id');

        $enrollments = Enrollment::query()
            ->whereIn('course_class_id', $classIds)
            ->with(['user', 'courseClass'])
            ->orderByRaw("CASE status WHEN 'confirmed' THEN 0 WHEN 'pending' THEN 1 ELSE 2 END")
            ->orderByDesc('id')
            ->get()
            ->map(function (Enrollment $enrollment): array {
                $user = $enrollment->user;

                return [
                    'id' => $enrollment->id,
                    'status' => $enrollment->status?->value ?? EnrollmentStatus::Pending->value,
                    'status_label' => $enrollment->status_label,
                    'class_name' => (string) ($enrollment->courseClass?->name ?? ''),
                    'confirmed_at' => $enrollment->confirmed_at?->toIso8601String(),
                    'user' => [
                        'id' => (int) ($user?->id ?? 0),
                        'name' => trim((string) ($user?->name ?? '').' '.($user?->family ?? '')) ?: '—',
                        'mobile' => $user?->mobile,
                        'email' => $user?->email,
                    ],
                ];
            })
            ->values()
            ->all();

        return Inertia::render('Instructor/Courses/Roster', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
            ],
            'enrollments' => $enrollments,
        ]);
    }
}
