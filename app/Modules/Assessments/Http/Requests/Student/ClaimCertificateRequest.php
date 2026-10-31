<?php

namespace App\Modules\Assessments\Http\Requests\Student;

use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\Enrollment;
use Illuminate\Foundation\Http\FormRequest;

class ClaimCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        /** @var Course $course */
        $course = $this->route('course');

        if ($user === null || ! $user->can('student-panel-access') || ! $course->certificate_enabled) {
            return false;
        }

        $classIds = $course->courseClasses()->pluck('id');

        return Enrollment::query()
            ->whereIn('course_class_id', $classIds)
            ->where('user_id', $user->id)
            ->where('status', EnrollmentStatus::Confirmed)
            ->exists();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
