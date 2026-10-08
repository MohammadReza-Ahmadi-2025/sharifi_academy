<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\AttributeValue;
use App\Modules\Catalog\Models\ProductVariant;
use Illuminate\Validation\ValidationException;

class VariantAttributeSync
{
    /**
     * @param  list<int|numeric-string>  $attributeValueIds
     */
    public function sync(ProductVariant $variant, array $attributeValueIds): void
    {
        $ids = collect($attributeValueIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            $variant->attributeValues()->sync([]);
            $variant->unsetRelation('attributeValues');
            $variant->forceFill([
                'combination_hash' => ProductVariant::manualCombinationHash(
                    $variant->product_id,
                    'empty-'.$variant->id,
                ),
            ])->saveQuietly();

            return;
        }

        $this->assertValidAttributeValues($ids);

        try {
            $variant->syncAttributeValuesWithHash($ids);
            $variant->unsetRelation('attributeValues');
        } catch (\DomainException $exception) {
            throw ValidationException::withMessages([
                'attribute_value_ids' => $exception->getMessage(),
            ]);
        }

        $this->refreshTitleFromAttributes($variant);
    }

    /**
     * @param  list<int>  $attributeValueIds
     */
    public function assertValidAttributeValues(array $attributeValueIds): void
    {
        $values = AttributeValue::query()
            ->with('attribute')
            ->whereIn('id', $attributeValueIds)
            ->get();

        if ($values->count() !== count($attributeValueIds)) {
            throw ValidationException::withMessages([
                'attribute_value_ids' => 'یک یا چند مقدار ویژگی معتبر نیست.',
            ]);
        }

        $attributeIds = [];

        foreach ($values as $value) {
            $attribute = $value->attribute;

            if ($attribute === null || ! $attribute->is_variant || ! $attribute->is_active) {
                throw ValidationException::withMessages([
                    'attribute_value_ids' => 'فقط ویژگی‌های واریانت مجاز هستند.',
                ]);
            }

            if (isset($attributeIds[$attribute->id])) {
                throw ValidationException::withMessages([
                    'attribute_value_ids' => 'برای هر ویژگی (مثلاً رنگ) فقط یک مقدار انتخاب کنید.',
                ]);
            }

            $attributeIds[$attribute->id] = true;
        }
    }

    public function refreshTitleFromAttributes(ProductVariant $variant): void
    {
        $variant->loadMissing(['attributeValues.attribute']);

        $parts = $variant->attributeValues
            ->sortBy(fn (AttributeValue $value) => $value->attribute?->sort ?? 0)
            ->map(fn (AttributeValue $value) => $value->value)
            ->filter()
            ->values()
            ->all();

        if ($parts === []) {
            return;
        }

        $variant->forceFill(['title' => implode(' / ', $parts)])->saveQuietly();
    }
}
