<?php

namespace Tests\Feature\Catalog;

use App\Models\User;
use App\Modules\Catalog\Models\InventoryLog;
use App\Modules\Catalog\Models\Price;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Catalog\Services\AdminProductVariantService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VariantPriceHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_price_change_appends_price_row_and_closes_previous(): void
    {
        $product = Product::query()->create([
            'title' => 'کالا',
            'slug' => 'item-price-history',
            'status' => true,
            'is_visible' => true,
        ]);

        $variant = app(AdminProductVariantService::class)->create($product, [
            'title' => 'پیش‌فرض',
            'status' => true,
            'display_mode' => 'normal',
            'price' => 1_000_000,
            'final_price' => 900_000,
            'stock' => 5,
        ]);

        $firstPriceId = $variant->currentPrice?->id;
        $this->assertNotNull($firstPriceId);

        app(AdminProductVariantService::class)->update($variant, [
            'title' => 'پیش‌فرض',
            'status' => true,
            'display_mode' => 'normal',
            'price' => 1_200_000,
            'final_price' => 1_100_000,
            'stock' => 5,
        ]);

        $this->assertSame(2, Price::query()->where('variant_id', $variant->id)->count());

        $closed = Price::query()->findOrFail($firstPriceId);
        $this->assertNotNull($closed->ends_at);

        $active = ProductVariant::query()->findOrFail($variant->id)->currentPrice;
        $this->assertNotNull($active);
        $this->assertSame(1_200_000, (int) $active->price);
        $this->assertNull($active->ends_at);
    }

    public function test_set_stock_writes_initial_inventory_log_when_stock_is_zero(): void
    {
        $product = Product::query()->create([
            'title' => 'کالا',
            'slug' => 'item-stock-log',
            'status' => true,
            'is_visible' => true,
        ]);

        $variant = app(AdminProductVariantService::class)->create($product, [
            'title' => 'پیش‌فرض',
            'status' => true,
            'display_mode' => 'normal',
            'price' => 0,
            'stock' => 0,
        ]);

        $this->assertSame(1, InventoryLog::query()->where('variant_id', $variant->id)->count());
        $this->assertSame('initial', InventoryLog::query()->where('variant_id', $variant->id)->value('type'));
    }

    public function test_admin_variant_update_via_http_keeps_price_history(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $product = Product::query()->create([
            'title' => 'HTTP',
            'slug' => 'http-price',
            'status' => true,
            'is_visible' => true,
        ]);

        $variant = app(AdminProductVariantService::class)->create($product, [
            'title' => 'v1',
            'sku' => 'HTTP-SKU',
            'status' => true,
            'display_mode' => 'normal',
            'price' => 500_000,
            'stock' => 1,
        ]);

        $this->actingAs($admin)
            ->put('/admin/catalog/products/'.$product->id.'/variants/'.$variant->id, [
                'title' => 'v1',
                'sku' => 'HTTP-SKU',
                'status' => true,
                'display_mode' => 'normal',
                'price' => 600_000,
                'stock' => 1,
            ])
            ->assertRedirect();

        $this->assertSame(2, Price::query()->where('variant_id', $variant->id)->count());
    }
}
