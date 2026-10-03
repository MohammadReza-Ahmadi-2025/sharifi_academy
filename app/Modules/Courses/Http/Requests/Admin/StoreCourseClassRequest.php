<?php

namespace App\Modules\Courses\Http\Requests\Admin;

use App\Modules\Courses\Enums\ClassInstructorRole;
use App\Modules\Courses\Enums\CourseClassDeliveryType;
use App\Modules\Courses\Enums\CourseClassStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('course-classes-create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:64'],
            'status' => ['required', Rule::enum(CourseClassStatus::class)],
            'delivery_type' => ['nullable', Rule::enum(CourseClassDeliveryType::class)],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'price' => ['nullable', 'integer', 'min:0'],
            'registration_starts_at' => ['nullable', 'date'],
            'registration_ends_at' => ['nullable', 'date', 'after_or_equal:registration_starts_at'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_public' => ['boolean'],
            'instructors' => ['nullable', 'array'],
            'instructors.*.user_id' => ['required', 'integer', 'exists:users,id'],
            'instructors.*.role' => ['required', Rule::enum(ClassInstructorRole::class)],
            'instructors.*.is_primary' => ['boolean'],
            'instructors.*.share_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
