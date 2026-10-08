<?php

namespace App\Modules\Catalog\Http\Requests\Admin\Concerns;

use App\Modules\Catalog\Enums\VariantDisplayMode;
use Illuminate\Validation\Rule;

trait ValidatesProductVariants
{
    /**
     * @return array<string, mixed>
     */
    protected function productVariantRules(): array
    {
        return [
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'variants.*.sku' => ['nullable', 'string', 'max:255'],
            'variants.*.attributes' => ['nullable', 'array'],
            'variants.*.attributes.*' => ['nullable', 'integer', 'exists:attribute_values,id'],
            'variants.*.price' => ['nullable', 'integer', 'min:0'],
            'variants.*.discount_type' => ['nullable', 'string', Rule::in(['percentage', 'fixed'])],
            'variants.*.discount_value' => ['nullable', 'integer', 'min:0'],
            'variants.*.final_price' => ['nullable', 'integer', 'min:0'],
            'variants.*.display_mode' => ['nullable', Rule::enum(VariantDisplayMode::class)],
            'variants.*.sale_start' => ['nullable', 'string', 'max:32'],
            'variants.*.sale_end' => ['nullable', 'string', 'max:32'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'variants.*.min_stock' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function productVariantsPayload(): array
    {
        $variants = $this->input('variants');

        if (! is_array($variants)) {
            return [];
        }

        return collect($variants)
            ->filter(fn ($row) => is_array($row))
            ->values()
            ->map(function (array $row) {
                $attributes = collect($row['attributes'] ?? [])
                    ->mapWithKeys(function ($value, $key) {
                        if ($value === null || $value === '') {
                            return [];
                        }

                        return [(string) $key => (int) $value];
                    })
                    ->all();

                return [
                    'id' => isset($row['id']) && $row['id'] !== '' ? (int) $row['id'] : null,
                    'sku' => filled($row['sku'] ?? null) ? (string) $row['sku'] : null,
                    'attributes' => $attributes,
                    'price' => isset($row['price']) && $row['price'] !== '' ? (int) $row['price'] : 0,
                    'discount_type' => filled($row['discount_type'] ?? null) ? (string) $row['discount_type'] : null,
                    'discount_value' => isset($row['discount_value']) && $row['discount_value'] !== ''
                        ? (int) $row['discount_value']
                        : null,
                    'final_price' => isset($row['final_price']) && $row['final_price'] !== ''
                        ? (int) $row['final_price']
                        : null,
                    'display_mode' => $row['display_mode'] ?? VariantDisplayMode::NORMAL->value,
                    'sale_start' => filled($row['sale_start'] ?? null) ? (string) $row['sale_start'] : null,
                    'sale_end' => filled($row['sale_end'] ?? null) ? (string) $row['sale_end'] : null,
                    'stock' => isset($row['stock']) && $row['stock'] !== '' ? (int) $row['stock'] : 0,
                    'min_stock' => isset($row['min_stock']) && $row['min_stock'] !== '' ? (int) $row['min_stock'] : 0,
                ];
            })
            ->all();
    }
}
