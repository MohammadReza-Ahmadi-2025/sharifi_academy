<?php

namespace App\Modules\Catalog\Http\Requests\Admin;

use App\Modules\Catalog\Enums\VariantDisplayMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('catalog-products-update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255', 'unique:product_variants,sku'],
            'barcode' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'boolean'],
            'display_mode' => ['required', Rule::enum(VariantDisplayMode::class)],
            'price' => ['required', 'integer', 'min:0'],
            'final_price' => ['nullable', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'attribute_value_ids' => ['nullable', 'array', 'max:3'],
            'attribute_value_ids.*' => ['integer', 'exists:attribute_values,id'],
        ];
    }
}
