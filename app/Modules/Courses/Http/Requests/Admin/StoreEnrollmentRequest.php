<?php

namespace App\Modules\Courses\Http\Requests\Admin;

use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Models\CourseClass;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('enrollments-create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var CourseClass|null $courseClass */
        $courseClass = $this->route('courseClass');

        return [
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
                Rule::unique('enrollments', 'user_id')->where(
                    fn ($query) => $query->where('course_class_id', $courseClass?->id),
                ),
            ],
            'status' => ['required', Rule::enum(EnrollmentStatus::class)],
            'price_amount' => ['nullable', 'integer', 'min:0'],
            'admin_note' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'user_id' => 'کاربر',
            'status' => 'وضعیت',
            'price_amount' => 'مبلغ',
            'admin_note' => 'یادداشت',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.unique' => 'این کاربر قبلاً در این کلاس ثبت‌نام شده است.',
        ];
    }
}
