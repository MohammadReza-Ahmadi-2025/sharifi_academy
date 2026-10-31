<?php

namespace App\Modules\Assessments\Http\Requests\Instructor;

use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Support\CourseOwnership;
use Illuminate\Foundation\Http\FormRequest;

class IssueCertificateRequest extends FormRequest
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
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'force' => ['boolean'],
        ];
    }
}
