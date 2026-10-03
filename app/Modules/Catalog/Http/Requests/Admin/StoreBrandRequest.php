<?php

namespace App\Modules\Catalog\Http\Requests\Admin;

use App\Modules\Catalog\Http\Requests\Admin\Concerns\NormalizesCatalogSeoInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBrandRequest extends FormRequest
{
    use NormalizesCatalogSeoInput;

    protected function prepareForValidation(): void
    {
        $this->mergeCatalogSeoFields();
    }

    public function authorize(): bool
    {
        return $this->user()?->can('catalog-brands-create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('brands', 'slug')],
            'description' => ['nullable', 'string', 'max:5000'],
            'website' => ['nullable', 'string', 'max:255', 'url'],
            'status' => ['sometimes', 'boolean'],
            'sort' => ['nullable', 'integer', 'min:0', 'max:99999'],
            ...$this->catalogSeoRules(),
        ];
    }
}
