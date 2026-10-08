<?php

namespace Tests\Feature;

use App\Modules\Settings\Models\SiteSetting;
use App\Modules\Settings\Services\SiteSettingsService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSettingsPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_shares_site_settings_and_app_name(): void
    {
        $this->seed(DatabaseSeeder::class);

        SiteSetting::query()->where('key', 'site_name')->update(['value' => 'آکادمی تست']);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('app.name', 'آکادمی تست')
                ->where('siteSettings.site_name', 'آکادمی تست')
            );
    }

    public function test_maintenance_mode_returns_maintenance_page(): void
    {
        $this->seed(DatabaseSeeder::class);

        SiteSetting::query()->where('key', 'maintenance_mode')->update(['value' => '1']);
        app(SiteSettingsService::class)->forgetCache();

        $this->get('/')
            ->assertStatus(503)
            ->assertInertia(fn ($page) => $page->component('Public/Maintenance'));

        $this->get('/admin')
            ->assertRedirect('/login');
    }
}
