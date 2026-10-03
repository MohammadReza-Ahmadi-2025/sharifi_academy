<?php

namespace App\Modules\Courses\Http\Requests\Admin;

class UpdateCourseClassRequest extends StoreCourseClassRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('course-classes-update') ?? false;
    }
}
