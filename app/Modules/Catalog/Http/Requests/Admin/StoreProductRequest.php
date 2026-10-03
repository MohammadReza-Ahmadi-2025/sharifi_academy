<?php

namespace App\Modules\Catalog\Http\Requests\Admin;

use App\Modules\Catalog\Http\Requests\Admin\Concerns\NormalizesCatalogSeoInput;
use App\Modules\Catalog\Http\Requests\Admin\Concerns\ValidatesInitialProductVariant;
use App\Modules\Catalog\Models\Category;
use App\Modules\Files\Http\Requests\Concerns\ValidatesGalleryUploads;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    use NormalizesCatalogSeoInput;
    use ValidatesGalleryUploads;
    use ValidatesInitialProductVariant;

    protected function prepareForValidation(): void
    {
        $this->mergeCatalogSeoFields();
    }

    public function authorize(): bool
    {
        return $this->user()?->can('catalog-products-create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'sku' => ['nullable', 'string', 'max:255', 'unique:products,sku'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'model' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'boolean'],
            'is_visible' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'integer', 'min:0'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => [
                'integer',
                Rule::exists('categories', 'id')->where('type', Category::TYPE_PRODUCT),
            ],
            'features' => ['nullable', 'string'],
            ...$this->catalogSeoRules(),
            ...$this->galleryImageRules(),
            ...$this->initialVariantRules(),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $this->assertMinimumOneImage($validator, 'gallery');
    }
}
