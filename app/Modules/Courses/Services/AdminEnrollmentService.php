<?php

namespace App\Modules\Courses\Services;

use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use App\Support\Notifications\InstructorModerationNotifier;

class AdminEnrollmentService
{
    /**
     * @param  array{user_id: int, status: string, price_amount?: int|null, admin_note?: string|null}  $data
     */
    public function createForClass(CourseClass $courseClass, array $data): Enrollment
    {
        $status = EnrollmentStatus::from($data['status']);

        $enrollment = Enrollment::query()->create([
            'course_class_id' => $courseClass->id,
            'user_id' => $data['user_id'],
            'status' => $status,
            'price_amount' => $data['price_amount'] ?? $courseClass->price ?? 0,
            'admin_note' => $data['admin_note'] ?? null,
            'confirmed_at' => $status === EnrollmentStatus::Confirmed ? now() : null,
            'cancelled_at' => $status === EnrollmentStatus::Cancelled ? now() : null,
        ]);

        if ($status === EnrollmentStatus::Confirmed) {
            InstructorModerationNotifier::enrollmentConfirmed($enrollment);
        }

        return $enrollment;
    }
}
