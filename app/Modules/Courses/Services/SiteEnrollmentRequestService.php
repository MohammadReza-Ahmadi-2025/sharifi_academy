<?php

namespace App\Modules\Courses\Services;

use App\Models\User;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use App\Support\Notifications\InstructorModerationNotifier;
use Illuminate\Validation\ValidationException;

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
        $existing = Enrollment::query()
            ->where('course_class_id', $courseClass->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing !== null) {
            if (in_array($existing->status, [
                EnrollmentStatus::Pending,
                EnrollmentStatus::Confirmed,
            ], true)) {
                throw ValidationException::withMessages([
                    'course_class_id' => 'شما قبلاً برای این کلاس درخواست داده‌اید.',
                ]);
            }

            $existing->update([
                'status' => EnrollmentStatus::Pending,
                'price_amount' => is_numeric($courseClass->price) ? (int) $courseClass->price : 0,
                'confirmed_at' => null,
                'cancelled_at' => null,
            ]);

            $enrollment = $existing->fresh();
        } else {
            $enrollment = Enrollment::query()->create([
                'course_class_id' => $courseClass->id,
                'user_id' => $user->id,
                'status' => EnrollmentStatus::Pending,
                'price_amount' => is_numeric($courseClass->price) ? (int) $courseClass->price : 0,
            ]);
        }

        InstructorModerationNotifier::enrollmentPendingForAdmin($enrollment);

        return $enrollment;
    }
}
