<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Enums\VariantDisplayMode;
use App\Modules\Catalog\Models\AttributeValue;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ProductVariantCombinationService
{
    public function __construct(
        private readonly AdminProductVariantService $variantService,
        private readonly VariantAttributeSync $attributeSync,
    ) {}

    /**
     * @param  list<array{attribute_id: int, attribute_value_ids: list<int>}>  $selections
     */
    public function generate(Product $product, array $selections, ?int $userId = null): int
    {
        if ($selections === []) {
            throw ValidationException::withMessages([
                'selections' => 'حداقل یک ویژگی با مقدار انتخاب کنید.',
            ]);
        }

        $combinations = $this->buildCombinations($selections);

        if ($combinations->isEmpty()) {
            throw ValidationException::withMessages([
                'selections' => 'ترکیبی برای ساخت پیدا نشد.',
            ]);
        }

        if ($combinations->count() > 300) {
            throw ValidationException::withMessages([
                'selections' => 'حداکثر ۳۰۰ ترکیب در هر بار مجاز است.',
            ]);
        }

        $created = 0;

        foreach ($combinations as $valueIds) {
            $hash = ProductVariant::combinationHashFor($valueIds);

            $exists = ProductVariant::query()
                ->where('product_id', $product->id)
                ->where('combination_hash', $hash)
                ->exists();

            if ($exists) {
                continue;
            }

            $variant = $this->variantService->create($product, [
                'title' => $this->titleForValueIds($valueIds),
                'status' => true,
                'display_mode' => VariantDisplayMode::NORMAL->value,
                'price' => 0,
                'stock' => 0,
            ], $userId);

            $this->attributeSync->sync($variant, $valueIds);
            $created++;
        }

        return $created;
    }

    /**
     * @param  list<array{attribute_id: int, attribute_value_ids: list<int>}>  $selections
     * @return Collection<int, list<int>>
     */
    private function buildCombinations(array $selections): Collection
    {
        $groups = [];

        foreach ($selections as $index => $selection) {
            $attributeId = (int) ($selection['attribute_id'] ?? 0);
            $valueIds = collect($selection['attribute_value_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter(fn (int $id) => $id > 0)
                ->unique()
                ->values()
                ->all();

            if ($attributeId <= 0 || $valueIds === []) {
                throw ValidationException::withMessages([
                    "selections.$index.attribute_value_ids" => 'برای هر ویژگی حداقل یک مقدار انتخاب کنید.',
                ]);
            }

            $validCount = AttributeValue::query()
                ->where('attribute_id', $attributeId)
                ->whereIn('id', $valueIds)
                ->where('is_active', true)
                ->count();

            if ($validCount !== count($valueIds)) {
                throw ValidationException::withMessages([
                    "selections.$index.attribute_value_ids" => 'مقادیر انتخاب‌شده با ویژگی هم‌خوان نیست.',
                ]);
            }

            $groups[] = $valueIds;
        }

        $combinations = collect([[]]);

        foreach ($groups as $valueIds) {
            $next = collect();

            foreach ($combinations as $partial) {
                foreach ($valueIds as $valueId) {
                    $next->push([...$partial, $valueId]);
                }
            }

            $combinations = $next;
        }

        return $combinations
            ->map(fn (array $ids) => collect($ids)->sort()->values()->all())
            ->unique(fn (array $ids) => implode('-', $ids))
            ->values();
    }

    /**
     * @param  list<int>  $valueIds
     */
    private function titleForValueIds(array $valueIds): string
    {
        return AttributeValue::query()
            ->with('attribute')
            ->whereIn('id', $valueIds)
            ->get()
            ->sortBy(fn (AttributeValue $value) => $value->attribute?->sort ?? 0)
            ->map(fn (AttributeValue $value) => $value->value)
            ->filter()
            ->implode(' / ');
    }
}
