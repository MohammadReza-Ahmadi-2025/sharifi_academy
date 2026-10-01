<?php

namespace Tests\Feature\Catalog;

use App\Modules\Catalog\Models\Attribute;
use App\Modules\Catalog\Models\AttributeValue;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Inventory;
use App\Modules\Catalog\Models\Price;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Catalog\Models\Warehouse;
use App\Modules\Catalog\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_variant_price_and_inventory_graph(): void
    {
        $category = Category::query()->create([
            'title' => 'دوره‌ها',
            'slug' => 'courses',
            'type' => 'product',
            'status' => 1,
        ]);

        $product = Product::query()->create([
            'title' => 'دوره لاراول',
            'slug' => 'laravel-course',
            'status' => true,
            'is_visible' => true,
        ]);

        $product->categories()->attach($category->id);

        $attribute = Attribute::query()->create([
            'name' => 'نوع دسترسی',
            'slug' => 'access-type',
            'is_variant' => true,
        ]);

        $value = AttributeValue::query()->create([
            'attribute_id' => $attribute->id,
            'value' => 'آنلاین',
            'slug' => 'online',
        ]);

        $variant = ProductVariant::query()->create([
            'product_id' => $product->id,
            'sku' => 'LRV-001',
            'status' => true,
        ]);

        $variant->syncAttributeValuesWithHash([$value->id]);

        Price::query()->create([
            'variant_id' => $variant->id,
            'price' => 1_500_000,
            'final_price' => 1_200_000,
            'discount_type' => 'percentage',
            'discount_value' => 20,
        ]);

        $warehouseId = Warehouse::defaultId();

        Inventory::query()->create([
            'variant_id' => $variant->id,
            'warehouse_id' => $warehouseId,
            'stock' => 10,
            'reserved' => 0,
        ]);

        $product->refresh()->load(['categories', 'variants.currentPrice', 'variants.inventories']);

        $this->assertCount(1, $product->categories);
        $this->assertSame('LRV-001', $product->variants->first()?->sku);
        $this->assertSame(10, app(InventoryService::class)->availableQuantity($variant->id));
        $this->assertNotNull($product->variants->first()?->currentPrice);
    }
}
