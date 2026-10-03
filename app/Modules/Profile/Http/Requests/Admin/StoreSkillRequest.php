<?php

namespace App\Modules\Profile\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSkillRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->input('category_id') === '' || $this->input('category_id') === null) {
            $this->merge(['category_id' => null]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->can('skills-create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('skills', 'name')],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('type', 'course')],
        ];
    }
}
