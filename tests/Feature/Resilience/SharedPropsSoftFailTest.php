<?php

namespace Tests\Feature\Resilience;

use App\Modules\Menu\Services\SiteMenuService;
use App\Modules\Settings\Services\SiteSettingsService;
use App\Modules\Social\Services\SiteSocialService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class SharedPropsSoftFailTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_when_shared_chrome_services_fail(): void
    {
        $this->mock(SiteSettingsService::class, function ($mock): void {
            $mock->shouldReceive('get')->andThrow(new RuntimeException('settings unavailable'));
            $mock->shouldReceive('autoload')->andThrow(new RuntimeException('settings unavailable'));
        });

        $this->mock(SiteMenuService::class, function ($mock): void {
            $mock->shouldReceive('groupedByLocation')->andThrow(new RuntimeException('menus unavailable'));
        });

        $this->mock(SiteSocialService::class, function ($mock): void {
            $mock->shouldReceive('forSite')->andThrow(new RuntimeException('socials unavailable'));
        });

        $this->get('/login')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Auth/OtpAuth')
                ->where('siteMenus', [])
                ->where('siteSocials', [])
                ->where('siteSettings', []));
    }
}
