<?php

namespace App\Modules\Courses\Http\Requests\Admin;

use App\Modules\Courses\Enums\EnrollmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('enrollments-update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(EnrollmentStatus::class)],
            'price_amount' => ['nullable', 'integer', 'min:0'],
            'admin_note' => ['nullable', 'string'],
        ];
    }
}
