<?php

namespace App\Modules\Social\Http\Requests\Admin;

use App\Modules\Social\Support\AdminSocialTypeOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSocialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('socials-update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(array_keys(AdminSocialTypeOptions::map()))],
            'value' => ['required', 'string', 'max:255'],
            'label' => ['nullable', 'string', 'max:255'],
            'target_blank' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
