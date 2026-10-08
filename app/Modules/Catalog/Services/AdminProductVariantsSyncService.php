<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Enums\VariantDisplayMode;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class AdminProductVariantsSyncService
{
    public function __construct(
        private readonly AdminProductVariantService $variantService,
        private readonly VariantAttributeSync $variantAttributeSync,
        private readonly VariantPriceService $variantPriceService,
        private readonly InventoryService $inventoryService,
    ) {}

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    public function sync(Product $product, array $rows, ?int $userId = null): void
    {
        if ($rows === []) {
            if (! $product->variants()->exists()) {
                $this->variantService->createDefault($product, $userId);
            }

            return;
        }

        DB::transaction(function () use ($product, $rows, $userId): void {
            $product->load([
                'variants.attributeValues',
                'variants.currentPrice',
                'variants.inventory',
            ]);

            $usedVariantIds = [];

            foreach ($rows as $index => $row) {
                $attributeValueIds = collect($row['attributes'] ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->filter(fn (int $id) => $id > 0)
                    ->sort()
                    ->values()
                    ->all();

                $variant = $this->resolveVariant($product, $row, $attributeValueIds);

                $usedVariantIds[] = $variant->id;

                $variant->update([
                    'sku' => $row['sku'] ?? $variant->sku,
                    'status' => true,
                    'display_mode' => $this->normalizeDisplayMode($row['display_mode'] ?? null),
                    'sort' => $index + 1,
                ]);

                $this->variantAttributeSync->sync($variant, $attributeValueIds);
                $this->variantPriceService->applyAdminVariantPricing($variant, $row);
                $this->inventoryService->setStockAndMin(
                    $variant->id,
                    (int) ($row['stock'] ?? 0),
                    (int) ($row['min_stock'] ?? 0),
                    null,
                    $userId,
                );
            }

            $product->variants()
                ->whereNotIn('id', $usedVariantIds)
                ->update(['status' => false]);
        });
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  list<int>  $attributeValueIds
     */
    private function resolveVariant(Product $product, array $row, array $attributeValueIds): ProductVariant
    {
        $variant = null;

        if (! empty($row['id'])) {
            $variant = $product->variants->firstWhere('id', (int) $row['id']);
        }

        if ($variant === null && $attributeValueIds !== []) {
            $variant = $product->variants->first(function (ProductVariant $candidate) use ($attributeValueIds): bool {
                $dbIds = $candidate->attributeValues
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->sort()
                    ->values()
                    ->all();

                return $dbIds === $attributeValueIds;
            });
        }

        if ($variant === null && $attributeValueIds === []) {
            $variant = $product->variants->first(function (ProductVariant $candidate): bool {
                return $candidate->attributeValues->isEmpty();
            });
        }

        if ($variant === null) {
            $variant = $product->variants()->create([
                'sku' => $row['sku'] ?? null,
                'status' => true,
                'display_mode' => $this->normalizeDisplayMode($row['display_mode'] ?? null),
                'sort' => (int) $product->variants()->max('sort') + 1,
                'combination_hash' => ProductVariant::manualCombinationHash(
                    $product->id,
                    uniqid('new-', true),
                ),
            ]);

            $product->load('variants.attributeValues');
        }

        return $variant;
    }

    private function normalizeDisplayMode(mixed $mode): string
    {
        if (is_string($mode) && VariantDisplayMode::tryFrom($mode) !== null) {
            return $mode;
        }

        return VariantDisplayMode::NORMAL->value;
    }
}
