<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Menu\Enums\MenuLocation;
use App\Modules\Menu\Models\Menu;
use App\Modules\Menu\Services\SiteMenuService;
use App\Support\SiteNavigation;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_navigation_comes_from_header_menus_in_database(): void
    {
        $this->seed(DatabaseSeeder::class);

        $nav = SiteNavigation::forUser(null);
        $labels = collect($nav['items'])->pluck('label')->all();

        $this->assertContains('خانه', $labels);
        $this->assertContains('دوره‌ها', $labels);
        $this->assertContains('ورود / ثبت‌نام', $labels);
        $this->assertNull($nav['cta']);
    }

    public function test_header_menu_with_children_becomes_mega_nav_item(): void
    {
        $menu = Menu::query()->create([
            'title' => 'خدمات',
            'url' => '/services',
            'location' => MenuLocation::Header,
            'sort' => 5,
            'status' => true,
        ]);

        $menu->allItems()->create([
            'title' => 'مشاوره',
            'type' => 'internal',
            'url' => '/consult',
            'sort' => 0,
            'status' => true,
        ]);

        SiteMenuService::flush();

        $nav = SiteNavigation::forUser(null);
        $item = collect($nav['items'])->firstWhere('id', 'menu-'.$menu->id);

        $this->assertNotNull($item);
        $this->assertSame('mega', $item['type']);
        $this->assertSame('مشاوره', $item['mega']['columns'][0]['links'][0]['label']);
    }

    public function test_navigation_is_shared_with_inertia(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('navigation.items')
                ->where('navigation.cta', null)
            );
    }

    public function test_navigation_does_not_depend_on_config_file(): void
    {
        Menu::query()->create([
            'title' => 'تست منو',
            'url' => '/test-menu',
            'location' => MenuLocation::Header,
            'sort' => 0,
            'status' => true,
        ]);

        SiteMenuService::flush();

        $nav = SiteNavigation::forUser(User::factory()->create());
        $this->assertContains('تست منو', collect($nav['items'])->pluck('label')->all());
    }

    public function test_authenticated_user_does_not_see_login_menu_item(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::factory()->create([
            'name' => 'علی',
            'family' => 'رضایی',
        ]);
        $user->syncRoles(['student']);

        $nav = SiteNavigation::forUser($user);
        $labels = collect($nav['items'])->pluck('label')->all();

        $this->assertNotContains('ورود / ثبت‌نام', $labels);
    }

    public function test_mobile_menus_are_exposed_in_site_menus(): void
    {
        Menu::query()->create([
            'title' => 'تب موبایل تست',
            'url' => '/test-mobile-tab',
            'icon' => 'shop',
            'location' => MenuLocation::Mobile,
            'sort' => 99,
            'status' => true,
        ]);

        SiteMenuService::flush();

        $mobile = app(SiteMenuService::class)->forLocation(MenuLocation::Mobile->value);
        $tab = collect($mobile)->firstWhere('title', 'تب موبایل تست');

        $this->assertNotNull($tab);
        $this->assertSame('/test-mobile-tab', $tab['url']);
        $this->assertSame('shop', $tab['icon']);
    }

    public function test_site_account_menu_is_shared_when_authenticated(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::factory()->create(['name' => 'مدیر', 'family' => 'سیستم']);
        $user->syncRoles(['super-admin']);

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('auth.account.display_name', 'مدیر سیستم')
                ->where('auth.account.role_label', 'مدیر سیستم')
                ->where('auth.account.panel.label', 'ورود به پنل ادمین')
                ->where('auth.account.panel.url', '/admin')
            );
    }
}
