<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Menu\Models\Menu;
use App\Modules\Menu\Services\SiteMenuService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdminMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_menu_and_clears_site_menu_cache(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        Cache::put('site:menus', ['cached'], 60);

        $this->actingAs($admin)
            ->post('/admin/menus', [
                'title' => 'لینک‌های تست',
                'location' => 'footer',
                'status' => true,
                'sort' => 99,
                'items' => [
                    [
                        'title' => 'درباره ما',
                        'type' => 'internal',
                        'url' => '/about',
                        'status' => true,
                        'target_blank' => false,
                    ],
                ],
            ])
            ->assertRedirect(route('admin.menus.index'));

        $menu = Menu::query()->where('title', 'لینک‌های تست')->first();
        $this->assertNotNull($menu);
        $this->assertSame(1, $menu->allItems()->count());
        $this->assertFalse(Cache::has('site:menus'));

        $grouped = app(SiteMenuService::class)->groupedByLocation();
        $this->assertNotEmpty($grouped['footer'] ?? []);

        $this->actingAs($admin)
            ->get('/admin/menus')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Menus/Index'));
    }
}
