<?php

namespace Tests\Feature\Locations;

use App\Modules\Locations\Models\ProvinceCity;
use App\Models\User;
use App\Modules\Profile\Models\UserProfile;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_one_profile_with_city(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('mobile', '09120000003')->firstOrFail();

        $province = ProvinceCity::query()->create([
            'title' => 'اصفهان',
            'user_id' => $user->id,
        ]);

        $city = ProvinceCity::query()->create([
            'title' => 'اصفهان',
            'parent_id' => $province->id,
            'user_id' => $user->id,
        ]);

        $profile = UserProfile::query()->create([
            'user_id' => $user->id,
            'headline' => 'دانشجوی دوره لاراول',
            'bio' => 'علاقه‌مند به بک‌اند',
            'national_code' => '1234567890',
            'province_city_id' => $city->id,
            'is_public' => true,
            'is_verified' => false,
        ]);

        $user->refresh();

        $this->assertNotNull($user->profile);
        $this->assertSame($profile->id, $user->profile->id);
        $this->assertSame($city->id, $user->profile->provinceCity?->id);
    }
}
