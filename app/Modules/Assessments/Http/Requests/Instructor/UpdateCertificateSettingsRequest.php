<?php

namespace App\Modules\Assessments\Http\Requests\Instructor;

use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Support\CourseOwnership;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCertificateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        /** @var Course $course */
        $course = $this->route('course');

        return $user !== null
            && $user->can('certificates-issue')
            && CourseOwnership::canManage($user, $course);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'certificate_enabled' => ['boolean'],
            'certificate_title' => ['nullable', 'string', 'max:255'],
            'certificate_min_attendance_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'certificate_min_quiz_score' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }
}
