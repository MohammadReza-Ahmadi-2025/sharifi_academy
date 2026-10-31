<?php

namespace Tests\Feature\Catalog;

use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicProductAndBrandTest extends TestCase
{
    use RefreshDatabase;

    public function test_visible_product_show_includes_product_json_ld(): void
    {
        $brand = Brand::query()->create([
            'title' => 'برند تست',
            'slug' => 'test-brand',
            'status' => true,
        ]);

        $product = Product::query()->create([
            'title' => 'کتاب تست SEO',
            'slug' => 'seo-test-book',
            'status' => true,
            'is_visible' => true,
            'brand_id' => $brand->id,
            'min_effective_price' => 250_000,
            'available_stock' => 3,
            'short_description' => 'توضیح کوتاه',
        ]);

        $this->get(route('books.show', $product))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/BookShow')
                ->where('product.title', 'کتاب تست SEO')
                ->where('structuredData.product.@type', 'Product')
                ->where('structuredData.product.name', 'کتاب تست SEO')
                ->where('structuredData.webPage.@type', 'WebPage')
                ->has('structuredData.breadcrumb'));
    }

    public function test_hidden_product_is_not_found(): void
    {
        $product = Product::query()->create([
            'title' => 'مخفی',
            'slug' => 'hidden-book',
            'status' => true,
            'is_visible' => false,
        ]);

        $this->get(route('books.show', $product))->assertNotFound();
    }

    public function test_brand_show_lists_products_and_json_ld(): void
    {
        $brand = Brand::query()->create([
            'title' => 'برند عمومی',
            'slug' => 'public-brand',
            'status' => true,
            'description' => 'توضیح برند',
        ]);

        Product::query()->create([
            'title' => 'محصول برند',
            'slug' => 'brand-product',
            'status' => true,
            'is_visible' => true,
            'brand_id' => $brand->id,
            'min_effective_price' => 100_000,
        ]);

        $this->get(route('brands.show', ['brand' => $brand->slug]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/BrandShow')
                ->where('brand.title', 'برند عمومی')
                ->where('structuredData.brand.@type', 'Brand')
                ->where('structuredData.webPage.@type', 'CollectionPage')
                ->has('products', 1)
                ->has('structuredData.itemList'));
    }
}
