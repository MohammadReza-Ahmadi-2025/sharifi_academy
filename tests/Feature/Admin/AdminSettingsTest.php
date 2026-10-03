<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Settings\Models\SiteSetting;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_and_update_site_setting(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();
        $setting = SiteSetting::query()->where('key', 'site_name')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/settings')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Settings/Index'));

        $this->actingAs($admin)
            ->put("/admin/settings/{$setting->id}", [
                'value' => 'آکادمی شریفی',
            ])
            ->assertRedirect(route('admin.settings.index', ['group' => 'general']));

        $setting->refresh();
        $this->assertSame('آکادمی شریفی', $setting->value);
    }

    public function test_student_cannot_access_settings(): void
    {
        $this->seed(DatabaseSeeder::class);
        $student = User::where('mobile', '09120000003')->firstOrFail();

        $this->actingAs($student)->get('/admin/settings')->assertForbidden();
    }
}
