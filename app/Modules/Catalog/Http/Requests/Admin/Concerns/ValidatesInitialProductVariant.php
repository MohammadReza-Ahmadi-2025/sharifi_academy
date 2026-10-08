<?php

namespace App\Modules\Catalog\Http\Requests\Admin\Concerns;

use App\Modules\Catalog\Enums\VariantDisplayMode;
use Illuminate\Validation\Rule;

trait ValidatesInitialProductVariant
{
    /**
     * @return array<string, mixed>
     */
    protected function initialVariantRules(): array
    {
        return [
            'variant' => ['nullable', 'array'],
            'variant.title' => ['nullable', 'string', 'max:255'],
            'variant.sku' => ['nullable', 'string', 'max:255', 'unique:product_variants,sku'],
            'variant.barcode' => ['nullable', 'string', 'max:255'],
            'variant.status' => ['nullable', 'boolean'],
            'variant.display_mode' => ['nullable', Rule::enum(VariantDisplayMode::class)],
            'variant.price' => ['nullable', 'integer', 'min:0'],
            'variant.final_price' => ['nullable', 'integer', 'min:0'],
            'variant.stock' => ['nullable', 'integer', 'min:0'],
            'variant.attribute_value_ids' => ['nullable', 'array', 'max:3'],
            'variant.attribute_value_ids.*' => ['integer', 'exists:attribute_values,id'],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function initialVariantPayload(): ?array
    {
        $variant = $this->input('variant');

        if (! is_array($variant) || ! $this->hasMeaningfulInitialVariant($variant)) {
            return null;
        }

        return [
            'title' => $variant['title'] ?? null,
            'sku' => $variant['sku'] ?? null,
            'barcode' => $variant['barcode'] ?? null,
            'status' => array_key_exists('status', $variant) ? (bool) $variant['status'] : true,
            'display_mode' => $variant['display_mode'] ?? VariantDisplayMode::NORMAL->value,
            'price' => isset($variant['price']) && $variant['price'] !== '' ? (int) $variant['price'] : 0,
            'final_price' => isset($variant['final_price']) && $variant['final_price'] !== ''
                ? (int) $variant['final_price']
                : null,
            'stock' => isset($variant['stock']) && $variant['stock'] !== '' ? (int) $variant['stock'] : 0,
            'attribute_value_ids' => collect($variant['attribute_value_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter(fn (int $id) => $id > 0)
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $variant
     */
    protected function hasMeaningfulInitialVariant(array $variant): bool
    {
        if (filled($variant['title'] ?? null) || filled($variant['sku'] ?? null) || filled($variant['barcode'] ?? null)) {
            return true;
        }

        if (isset($variant['price']) && $variant['price'] !== '' && (int) $variant['price'] > 0) {
            return true;
        }

        if (isset($variant['stock']) && $variant['stock'] !== '' && (int) $variant['stock'] > 0) {
            return true;
        }

        if (isset($variant['display_mode']) && $variant['display_mode'] !== '' && $variant['display_mode'] !== VariantDisplayMode::NORMAL->value) {
            return true;
        }

        if (collect($variant['attribute_value_ids'] ?? [])->filter()->isNotEmpty()) {
            return true;
        }

        return false;
    }
}
