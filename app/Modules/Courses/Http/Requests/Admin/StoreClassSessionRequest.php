<?php

namespace App\Modules\Courses\Http\Requests\Admin;

use App\Modules\Courses\Enums\ClassSessionStatus;
use App\Modules\Courses\Models\CourseClass;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('class-sessions-create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var CourseClass|null $courseClass */
        $courseClass = $this->route('courseClass');

        return [
            'session_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('class_sessions', 'session_number')
                    ->where('course_class_id', $courseClass?->id),
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(ClassSessionStatus::class)],
            'scheduled_start_at' => ['required', 'date'],
            'scheduled_end_at' => ['required', 'date', 'after:scheduled_start_at'],
            'actual_start_at' => ['nullable', 'date'],
            'actual_end_at' => ['nullable', 'date', 'after:actual_start_at'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'cancelled_at' => ['nullable', 'date'],
            'cancellation_reason' => ['nullable', 'string'],
        ];
    }
}
