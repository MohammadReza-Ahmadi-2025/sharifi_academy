<?php

namespace Tests\Feature\Admin;

use App\Modules\Locations\Models\ProvinceCity;
use App\Models\User;
use App\Modules\Users\Support\UserAvatarMedia;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_profile_page(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/profile')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Profile/Edit')
                ->where('profile.mobile', '09120000001')
            );
    }

    public function test_user_can_update_profile(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->put('/admin/profile', [
                'name' => 'مدیر جدید',
                'family' => 'تست',
                'mobile' => '09120000001',
                'email' => 'new@sharifi.test',
                'gender' => 'male',
            ])
            ->assertRedirect();

        $admin->refresh();
        $this->assertSame('مدیر جدید', $admin->name);
        $this->assertSame('new@sharifi.test', $admin->email);
    }

    public function test_user_can_change_mobile_and_verification_is_reset(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();
        $this->assertNotNull($admin->mobile_verified_at);

        $this->actingAs($admin)
            ->put('/admin/profile', [
                'name' => $admin->name,
                'family' => $admin->family,
                'mobile' => '09129998877',
                'email' => $admin->email,
                'gender' => $admin->gender,
            ])
            ->assertRedirect();

        $admin->refresh();
        $this->assertSame('09129998877', $admin->mobile);
        $this->assertNull($admin->mobile_verified_at);
    }

    public function test_profile_rejects_duplicate_mobile(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->put('/admin/profile', [
                'name' => $admin->name,
                'family' => $admin->family,
                'mobile' => '09120000002',
                'email' => $admin->email,
                'gender' => $admin->gender,
            ])
            ->assertSessionHasErrors('mobile');
    }

    public function test_profile_normalizes_persian_digits_in_mobile(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->put('/admin/profile', [
                'name' => $admin->name,
                'family' => $admin->family,
                'mobile' => '۰۹۱۲۱۱۱۲۲۳۳',
                'email' => $admin->email,
                'gender' => $admin->gender,
            ])
            ->assertRedirect();

        $admin->refresh();
        $this->assertSame('09121112233', $admin->mobile);
    }

    public function test_user_can_update_profile_avatar(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->put('/admin/profile', [
                'name' => $admin->name,
                'family' => $admin->family,
                'mobile' => $admin->mobile,
                'email' => $admin->email,
                'gender' => $admin->gender,
                'avatar' => UploadedFile::fake()->image('me.jpg', 100, 100)->size(400),
                'avatar_alt' => 'آواتار من',
            ])
            ->assertRedirect();

        $admin->refresh();
        $this->assertNotNull($admin->getFirstMedia(UserAvatarMedia::COLLECTION));
    }

    public function test_user_can_update_password(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->put('/admin/profile/password', [
                'current_password' => 'password',
                'password' => 'new-password-1',
                'password_confirmation' => 'new-password-1',
            ])
            ->assertRedirect();

        $admin->refresh();
        $this->assertTrue(Hash::check('new-password-1', $admin->password));
    }

    public function test_user_can_update_extended_profile(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();
        $city = ProvinceCity::query()->whereNotNull('parent_id')->where('title', 'اصفهان')->firstOrFail();

        $this->actingAs($admin)
            ->put('/admin/profile', [
                'name' => $admin->name,
                'family' => $admin->family,
                'mobile' => $admin->mobile,
                'email' => $admin->email,
                'gender' => $admin->gender,
                'headline' => 'مدیر آموزشگاه',
                'province_city_id' => $city->id,
                'is_public' => true,
            ])
            ->assertRedirect();

        $admin->load('profile');
        $this->assertSame('مدیر آموزشگاه', $admin->profile?->headline);
        $this->assertSame($city->id, $admin->profile?->province_city_id);
    }
}
