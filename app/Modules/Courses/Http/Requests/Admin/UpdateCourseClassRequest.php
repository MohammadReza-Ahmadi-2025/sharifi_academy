<?php

namespace App\Modules\Courses\Http\Requests\Admin;

use App\Modules\Courses\Models\CourseClass;
use Illuminate\Validation\Rule;

class UpdateCourseClassRequest extends StoreCourseClassRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('course-classes-update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        /** @var CourseClass|null $courseClass */
        $courseClass = $this->route('courseClass');

        $rules['code'] = [
            'required',
            'string',
            'max:80',
            Rule::unique('course_classes', 'code')->ignore($courseClass?->id),
        ];

        return $rules;
    }
}
