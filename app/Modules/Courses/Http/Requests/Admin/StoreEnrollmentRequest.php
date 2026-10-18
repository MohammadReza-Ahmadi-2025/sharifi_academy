<?php

namespace App\Modules\Courses\Http\Requests\Admin;

use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Http\Requests\Admin\Concerns\ValidatesEnrollableUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreEnrollmentRequest extends FormRequest
{
    use ValidatesEnrollableUser;

    public function authorize(): bool
    {
        return $this->user()?->can('enrollments-create') ?? false;
    }

    public function withValidator(Validator $validator): void
    {
        $this->withEnrollableUserValidator($validator);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
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
            'user_id' => 'کاربر',
            'status' => 'وضعیت',
            'price_amount' => 'مبلغ',
            'admin_note' => 'یادداشت',
        ];
    }
}
