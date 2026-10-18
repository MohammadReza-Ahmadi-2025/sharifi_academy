<?php

namespace App\Modules\Pages\Http\Requests\Admin;

use App\Modules\Catalog\Http\Requests\Admin\Concerns\NormalizesCatalogSeoInput;
use Illuminate\Foundation\Http\FormRequest;

class StorePageRequest extends FormRequest
{
    use NormalizesCatalogSeoInput;

    protected function prepareForValidation(): void
    {
        $this->mergeCatalogSeoFields();
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->can('pages-create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:pages,slug'],
            'content' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            ...$this->catalogSeoRules(),
        ];
    }
}
