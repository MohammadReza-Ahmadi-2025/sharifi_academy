<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Catalog\Models\Attribute;
use App\Modules\Catalog\Models\AttributeValue;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminProductVariantAttributesTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_color_is_variant_attribute_after_migration(): void
    {
        $this->seed(DatabaseSeeder::class);

        $color = Attribute::query()->where('slug', 'color')->firstOrFail();
        $size = Attribute::query()->where('slug', 'size')->firstOrFail();

        $this->assertTrue($color->is_variant);
        $this->assertFalse($size->is_variant);
    }

    public function test_admin_can_sync_variants_with_color_on_product_update(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $color = Attribute::query()->where('slug', 'color')->firstOrFail();
        $black = AttributeValue::query()->where('attribute_id', $color->id)->where('value', 'مشکی')->firstOrFail();
        $white = AttributeValue::query()->where('attribute_id', $color->id)->where('value', 'سفید')->firstOrFail();

        $product = Product::query()->create([
            'title' => 'محصول تست واریانت',
            'slug' => 'variant-test-product',
            'status' => true,
            'is_visible' => true,
        ]);

        ProductVariant::query()->create([
            'product_id' => $product->id,
            'title' => 'پیش‌فرض',
            'status' => true,
            'display_mode' => 'normal',
            'sort' => 1,
            'combination_hash' => ProductVariant::manualCombinationHash($product->id, 'default'),
        ]);

        $this->actingAs($admin)
            ->put("/admin/catalog/products/{$product->id}", [
                'title' => $product->title,
                'slug' => $product->slug,
                'status' => true,
                'is_visible' => true,
                'gallery' => [
                    ['file' => UploadedFile::fake()->image('product.jpg')],
                ],
                'variants' => [
                    [
                        'attributes' => [(string) $color->id => $black->id],
                        'price' => 100_000,
                        'stock' => 5,
                        'display_mode' => 'normal',
                    ],
                    [
                        'attributes' => [(string) $color->id => $white->id],
                        'price' => 110_000,
                        'stock' => 3,
                        'display_mode' => 'normal',
                    ],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame(2, ProductVariant::query()->where('product_id', $product->id)->where('status', true)->count());

        $blackVariant = ProductVariant::query()
            ->where('product_id', $product->id)
            ->where('status', true)
            ->whereHas('attributeValues', fn ($q) => $q->whereKey($black->id))
            ->with('attributeValues')
            ->first();

        $this->assertNotNull($blackVariant);
        $this->assertCount(1, $blackVariant->attributeValues);
        $this->assertStringContainsString('مشکی', (string) $blackVariant->title);
    }
}
