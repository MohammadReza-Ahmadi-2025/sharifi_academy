<?php

namespace App\Modules\Catalog\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GenerateProductVariantsRequest extends FormRequest
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
            'selections' => ['required', 'array', 'min:1', 'max:3'],
            'selections.*.attribute_id' => ['required', 'integer', 'exists:attributes,id'],
            'selections.*.attribute_value_ids' => ['required', 'array', 'min:1'],
            'selections.*.attribute_value_ids.*' => ['integer', 'exists:attribute_values,id'],
        ];
    }
}
