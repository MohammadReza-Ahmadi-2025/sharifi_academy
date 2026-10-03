<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Catalog\Services\InventoryService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_cannot_access_catalog_products(): void
    {
        $this->seed(DatabaseSeeder::class);
        $student = User::where('mobile', '09120000003')->firstOrFail();

        $this->actingAs($student)->get('/admin/catalog/products')->assertForbidden();
    }

    public function test_super_admin_can_manage_category_product_and_variant(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/categories', [
                'type' => Category::TYPE_PRODUCT,
                'title' => 'کتاب',
                'status' => true,
                'image' => [
                    0 => ['file' => UploadedFile::fake()->image('cat.jpg')],
                ],
            ])
            ->assertRedirect();

        $category = Category::query()->where('title', 'کتاب')->firstOrFail();
        $this->assertSame(Category::TYPE_PRODUCT, $category->type);

        $this->actingAs($admin)
            ->post('/admin/catalog/products', [
                'title' => 'لاراول پیشرفته',
                'status' => true,
                'is_visible' => true,
                'category_ids' => [$category->id],
                'gallery' => [
                    0 => ['file' => UploadedFile::fake()->image('product.jpg')],
                ],
            ])
            ->assertRedirect();

        $product = Product::query()->where('title', 'لاراول پیشرفته')->firstOrFail();
        $this->assertTrue($product->categories()->whereKey($category->id)->exists());
        $this->assertSame(1, $product->variants()->count());
        $this->assertSame('پیش‌فرض', $product->variants()->first()?->title);

        $this->actingAs($admin)
            ->post('/admin/catalog/products/'.$product->id.'/variants', [
                'title' => 'نسخه چاپی',
                'sku' => 'BOOK-LRV-1',
                'status' => true,
                'display_mode' => 'normal',
                'price' => 2_500_000,
                'final_price' => 2_000_000,
                'stock' => 12,
            ])
            ->assertRedirect();

        $variant = ProductVariant::query()->where('product_id', $product->id)->where('sku', 'BOOK-LRV-1')->firstOrFail();
        $this->assertSame('BOOK-LRV-1', $variant->sku);
        $this->assertSame(12, app(InventoryService::class)->availableQuantity($variant->id));

        $this->actingAs($admin)
            ->get('/admin/catalog/products/'.$product->id.'/edit')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Catalog/Products/Edit'));
    }
}
