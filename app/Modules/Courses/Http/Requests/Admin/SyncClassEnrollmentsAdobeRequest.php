<?php

namespace App\Modules\Courses\Http\Requests\Admin;

use App\Modules\Courses\Models\CourseClass;
use Illuminate\Foundation\Http\FormRequest;

class SyncClassEnrollmentsAdobeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $courseClass = $this->route('courseClass');

        return ($this->user()?->can('enrollments-update') ?? false)
            && $courseClass instanceof CourseClass;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'only_missing' => ['sometimes', 'boolean'],
        ];
    }
}
