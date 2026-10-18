<?php

namespace App\Modules\Profile\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RejectInstructorResumeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('instructor-moderation-update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
