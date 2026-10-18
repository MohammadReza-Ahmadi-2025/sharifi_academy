<?php

namespace App\Modules\Courses\Support;

use App\Models\User;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Profile\Support\UserProfileCompleteness;
use Illuminate\Support\Facades\Gate;

final class CourseEnrollmentUiPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forCourse(Course $course, ?User $user): array
    {
        $publicClasses = $course->relationLoaded('courseClasses')
            ? $course->courseClasses
            : $course->courseClasses()->public()->orderBy('starts_at')->get();

        $requestableClassIds = $publicClasses
            ->filter(fn (CourseClass $class) => $user !== null && Gate::forUser($user)->allows('requestEnrollment', $class))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if ($user === null) {
            return [
                'authenticated' => false,
                'profile_complete' => false,
                'profile_url' => route('site.profile.edit'),
                'login_url' => url('/login'),
                'state' => 'guest',
                'course_class_id' => null,
                'student_dashboard_url' => null,
                'requestable_class_ids' => [],
            ];
        }

        $enrollment = Enrollment::query()
            ->where('user_id', $user->id)
            ->whereIn('course_class_id', $publicClasses->pluck('id'))
            ->whereIn('status', [
                EnrollmentStatus::Pending,
                EnrollmentStatus::Confirmed,
            ])
            ->latest('id')
            ->first();

        $state = match (true) {
            $enrollment?->isConfirmed() => 'confirmed',
            $enrollment?->isPending() => 'pending',
            default => 'none',
        };

        return [
            'authenticated' => true,
            'profile_complete' => UserProfileCompleteness::isComplete($user),
            'profile_url' => route('site.profile.edit'),
            'login_url' => null,
            'state' => $state,
            'course_class_id' => $enrollment?->course_class_id,
            'student_dashboard_url' => $state === 'confirmed' ? route('student.dashboard') : null,
            'requestable_class_ids' => $state === 'none' ? $requestableClassIds : [],
        ];
    }
}
