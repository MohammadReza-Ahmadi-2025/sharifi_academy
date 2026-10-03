<?php

namespace App\Modules\Catalog\Http\Requests\Admin;

use App\Modules\Catalog\Http\Requests\Admin\Concerns\NormalizesCatalogSeoInput;
use App\Modules\Catalog\Models\Brand;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBrandRequest extends FormRequest
{
    use NormalizesCatalogSeoInput;

    protected function prepareForValidation(): void
    {
        $this->mergeCatalogSeoFields();
    }

    public function authorize(): bool
    {
        return $this->user()?->can('catalog-brands-update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $brand = $this->route('brand');
        $brandId = $brand instanceof Brand ? $brand->id : $brand;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('brands', 'slug')->ignore($brandId),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'website' => ['nullable', 'string', 'max:255', 'url'],
            'status' => ['sometimes', 'boolean'],
            'sort' => ['nullable', 'integer', 'min:0', 'max:99999'],
            ...$this->catalogSeoRules(),
        ];
    }
}
