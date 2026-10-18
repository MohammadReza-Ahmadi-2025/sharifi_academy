<?php

namespace App\Modules\Courses\Services;

use App\Models\User;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use App\Support\Notifications\InstructorModerationNotifier;

class SiteEnrollmentRequestService
{
    public function findRequestableClass(Course $course, int $courseClassId): ?CourseClass
    {
        return CourseClass::query()
            ->where('course_id', $course->id)
            ->whereKey($courseClassId)
            ->public()
            ->openForRegistration()
            ->first();
    }

    public function request(User $user, CourseClass $courseClass): Enrollment
    {
        $enrollment = Enrollment::query()->create([
            'course_class_id' => $courseClass->id,
            'user_id' => $user->id,
            'status' => EnrollmentStatus::Pending,
            'price_amount' => is_numeric($courseClass->price) ? (int) $courseClass->price : 0,
        ]);

        InstructorModerationNotifier::enrollmentPendingForAdmin($enrollment);

        return $enrollment;
    }
}
