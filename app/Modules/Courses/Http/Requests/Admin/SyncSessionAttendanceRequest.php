<?php

namespace App\Modules\Courses\Http\Requests\Admin;

use App\Modules\Courses\Enums\SessionAttendanceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncSessionAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('session-attendances-update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'attendances' => ['required', 'array'],
            'attendances.*.user_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('users', 'id'),
            ],
            'attendances.*.status' => ['required', 'string', Rule::in(SessionAttendanceStatus::values())],
            'attendances.*.note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'attendances.*.user_id.distinct' => 'هر کاربر فقط یک‌بار در لیست حضور می‌تواند باشد.',
        ];
    }
}
