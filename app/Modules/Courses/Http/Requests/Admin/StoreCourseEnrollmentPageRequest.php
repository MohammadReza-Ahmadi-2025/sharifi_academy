<?php

namespace App\Modules\Courses\Http\Requests\Admin;

use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCourseEnrollmentPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('enrollments-create') ?? false;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $courseId = $this->input('course_id');
            $classId = $this->input('course_class_id');

            if (! is_numeric($courseId) || ! is_numeric($classId)) {
                return;
            }

            $exists = CourseClass::query()
                ->whereKey((int) $classId)
                ->where('course_id', (int) $courseId)
                ->exists();

            if (! $exists) {
                $validator->errors()->add('course_class_id', 'کلاس انتخاب‌شده به این دوره تعلق ندارد.');

                return;
            }

            $userId = (int) $this->input('user_id');
            $duplicate = Enrollment::query()
                ->where('course_class_id', (int) $classId)
                ->where('user_id', $userId)
                ->exists();

            if ($duplicate) {
                $validator->errors()->add('user_id', 'این کاربر قبلاً در این کلاس ثبت‌نام شده است.');
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', Rule::exists('courses', 'id')],
            'course_class_id' => ['required', 'integer', Rule::exists('course_classes', 'id')],
            'user_id' => ['required', 'integer', 'exists:users,id'],
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
            'course_id' => 'دوره',
            'course_class_id' => 'کلاس',
            'user_id' => 'کاربر',
            'status' => 'وضعیت',
            'price_amount' => 'مبلغ',
            'admin_note' => 'یادداشت',
        ];
    }

    public function course(): Course
    {
        return Course::query()->findOrFail((int) $this->input('course_id'));
    }

    public function courseClass(): CourseClass
    {
        return CourseClass::query()
            ->where('course_id', (int) $this->input('course_id'))
            ->findOrFail((int) $this->input('course_class_id'));
    }
}
