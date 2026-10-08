<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Enums\VariantDisplayMode;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use Illuminate\Support\Str;

class AdminProductVariantService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly VariantPriceService $variantPriceService,
        private readonly VariantAttributeSync $variantAttributeSync,
    ) {}

    public function createDefault(Product $product, ?int $userId = null): ProductVariant
    {
        $sku = $product->sku;

        if ($sku !== null && ProductVariant::query()->where('sku', $sku)->exists()) {
            $sku = null;
        }

        return $this->create($product, [
            'title' => 'پیش‌فرض',
            'sku' => $sku,
            'status' => true,
            'display_mode' => VariantDisplayMode::NORMAL->value,
            'price' => 0,
            'stock' => 0,
        ], $userId);
    }

    /**
     * @param  array<string, mixed>|null  $data
     */
    public function ensureInitialVariant(Product $product, ?array $data, ?int $userId = null): ProductVariant
    {
        if ($product->variants()->exists()) {
            return $product->variants()->orderBy('sort')->orderBy('id')->firstOrFail();
        }

        if ($data !== null) {
            return $this->create($product, $data, $userId);
        }

        return $this->createDefault($product, $userId);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(Product $product, array $data, ?int $userId = null): ProductVariant
    {
        $seed = $data['sku'] ?? Str::uuid()->toString();

        $nextSort = (int) $product->variants()->max('sort') + 1;

        $variant = ProductVariant::query()->create([
            'product_id' => $product->id,
            'title' => $data['title'] ?? null,
            'sku' => $data['sku'] ?? null,
            'barcode' => $data['barcode'] ?? null,
            'combination_hash' => ProductVariant::manualCombinationHash($product->id, (string) $seed),
            'status' => $data['status'] ?? true,
            'display_mode' => $data['display_mode'],
            'sort' => $nextSort,
        ]);

        $this->applyAdminPricing($variant, $data);
        $this->inventoryService->setStock($variant->id, (int) ($data['stock'] ?? 0), null, $userId);
        $this->variantAttributeSync->sync($variant, $data['attribute_value_ids'] ?? []);

        return $variant->fresh(['currentPrice', 'inventory', 'attributeValues.attribute']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(ProductVariant $variant, array $data, ?int $userId = null): ProductVariant
    {
        $variant->update([
            'title' => $data['title'] ?? null,
            'sku' => $data['sku'] ?? null,
            'barcode' => $data['barcode'] ?? null,
            'status' => $data['status'] ?? true,
            'display_mode' => $data['display_mode'],
        ]);

        $this->applyAdminPricing($variant, $data);
        $this->inventoryService->setStock($variant->id, (int) ($data['stock'] ?? 0), null, $userId);
        $this->variantAttributeSync->sync($variant, $data['attribute_value_ids'] ?? []);

        return $variant->fresh(['currentPrice', 'inventory', 'attributeValues.attribute']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function applyAdminPricing(ProductVariant $variant, array $data): void
    {
        $price = (int) $data['price'];
        $finalPrice = isset($data['final_price']) && $data['final_price'] !== ''
            ? (int) $data['final_price']
            : $price;

        $this->variantPriceService->applyAdminPrice($variant, $price, $finalPrice);
    }
}
