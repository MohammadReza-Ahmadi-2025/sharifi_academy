<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Social\Models\Social;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSocialsPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_shares_active_social_links(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)->post('/admin/socials', [
            'type' => 'telegram',
            'value' => 'https://t.me/example',
            'label' => 'تلگرام آکادمی',
            'target_blank' => true,
            'is_active' => true,
        ])->assertRedirect();

        Social::query()->create([
            'type' => 'instagram',
            'value' => 'https://instagram.com/hidden',
            'label' => 'غیرفعال',
            'is_active' => false,
            'sort' => 99,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('siteSocials', 1)
                ->where('siteSocials.0.type', 'telegram')
                ->where('siteSocials.0.url', 'https://t.me/example')
                ->where('siteSocials.0.label', 'تلگرام آکادمی')
            );
    }
}
