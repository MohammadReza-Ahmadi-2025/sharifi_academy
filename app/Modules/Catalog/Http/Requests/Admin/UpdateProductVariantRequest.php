<?php

namespace App\Modules\Catalog\Http\Requests\Admin;

use App\Modules\Catalog\Enums\VariantDisplayMode;
use App\Modules\Catalog\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductVariantRequest extends FormRequest
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
        $variant = $this->route('variant');

        return [
            'title' => ['nullable', 'string', 'max:255'],
            'sku' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('product_variants', 'sku')->ignore($variant instanceof ProductVariant ? $variant->id : null),
            ],
            'barcode' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'boolean'],
            'display_mode' => ['required', Rule::enum(VariantDisplayMode::class)],
            'price' => ['required', 'integer', 'min:0'],
            'final_price' => ['nullable', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
        ];
    }
}
