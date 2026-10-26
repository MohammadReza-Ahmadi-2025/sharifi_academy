<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Catalog\Models\Brand;
use App\Modules\Pages\Models\Page;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlugPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_page_slug_is_normalized_before_save(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/pages', [
                'title' => 'درباره ما',
                'slug' => 'About Our Academy',
                'content' => 'متن',
                'is_active' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('pages', [
            'slug' => 'about-our-academy',
        ]);
    }

    public function test_admin_brand_slug_is_normalized_on_update(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $brand = Brand::query()->create([
            'title' => 'برند',
            'slug' => 'brand-base',
            'status' => true,
            'sort' => 1,
        ]);

        $this->actingAs($admin)
            ->put('/admin/catalog/brands/'.$brand->id, [
                'title' => 'برند',
                'slug' => 'New Brand Name',
                'status' => true,
                'sort' => 1,
            ])
            ->assertRedirect();

        $brand->refresh();
        $this->assertSame('new-brand-name', $brand->slug);
    }

    public function test_page_update_normalizes_slug_from_request(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $page = Page::query()->create([
            'title' => 'صفحه',
            'slug' => 'old-page',
            'content' => 'x',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->put('/admin/pages/'.$page->slug, [
                'title' => 'صفحه',
                'slug' => 'Updated Page Title',
                'content' => 'x',
                'is_active' => true,
            ])
            ->assertRedirect();

        $page->refresh();
        $this->assertSame('updated-page-title', $page->slug);
    }
}
