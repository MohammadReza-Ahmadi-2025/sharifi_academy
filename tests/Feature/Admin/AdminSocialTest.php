<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Social\Models\Social;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdminSocialTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_social_and_clears_cache(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        Cache::put('site_socials', ['cached'], 60);

        $this->actingAs($admin)
            ->post('/admin/socials', [
                'type' => 'telegram',
                'value' => 'https://t.me/example',
                'label' => 'تلگرام ما',
                'target_blank' => true,
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.socials.index'));

        $social = Social::query()->where('value', 'https://t.me/example')->first();
        $this->assertNotNull($social);
        $this->assertSame(1, $social->sort);
        $this->assertFalse(Cache::has('site_socials'));

        $this->actingAs($admin)
            ->get('/admin/socials')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Social/Index'));
    }
}
