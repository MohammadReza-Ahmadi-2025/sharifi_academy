<?php

namespace App\Modules\Home\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReorderHomeSectionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('home-sections-update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sections' => ['required', 'array', 'min:1'],
            'sections.*.id' => ['required', 'integer', 'exists:home_sections,id'],
            'sections.*.sort' => ['required', 'integer', 'min:0', 'max:9999'],
        ];
    }
}
