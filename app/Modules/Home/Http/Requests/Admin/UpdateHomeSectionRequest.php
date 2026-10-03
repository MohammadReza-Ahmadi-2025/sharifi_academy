<?php

namespace App\Modules\Home\Http\Requests\Admin;

use App\Modules\Home\Http\Requests\Admin\Concerns\HomeSectionTabValidation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHomeSectionRequest extends FormRequest
{
    use HomeSectionTabValidation;

    public function authorize(): bool
    {
        return $this->user()?->can('home-sections-update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge([
            'title' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'settings' => ['nullable', 'array'],
            'settings.layout' => ['nullable', 'string', 'max:50'],
            'settings.banner_layout' => ['nullable', 'string', 'max:50'],
        ], $this->homeSectionTabRules());
    }

    public function withValidator($validator): void
    {
        $this->validateHomeSectionTabs($validator);
    }
}
