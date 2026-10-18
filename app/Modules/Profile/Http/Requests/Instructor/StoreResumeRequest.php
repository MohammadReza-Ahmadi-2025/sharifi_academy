<?php

namespace App\Modules\Profile\Http\Requests\Instructor;

use App\Modules\Profile\Models\Resume;
use App\Modules\Profile\Support\ResumeAttachmentMedia;
use App\Modules\Profile\Support\ResumeTypeOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreResumeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Resume::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(ResumeTypeOptions::values())],
            'title' => ['required', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_current' => ['boolean'],
            'link' => ['nullable', 'string', 'max:500', 'url'],
            'description' => ['nullable', 'string', 'max:5000'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            ...ResumeAttachmentMedia::validationRules(),
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->boolean('is_current')) {
            $this->merge(['end_date' => null]);
        }
    }
}
