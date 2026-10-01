<?php

namespace Tests\Feature\Account;

use App\Modules\Locations\Models\ProvinceCity;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_site_profile(): void
    {
        $this->get('/profile')->assertRedirect('/login');
    }

    public function test_student_can_view_site_profile(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = User::where('mobile', '09120000003')->firstOrFail();

        $this->actingAs($student)
            ->get('/profile')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Account/Profile/Edit')
                ->where('profile.mobile', '09120000003')
            );
    }

    public function test_student_can_update_site_profile(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = User::where('mobile', '09120000003')->firstOrFail();

        $this->actingAs($student)
            ->put('/profile', [
                'name' => 'دانشجو',
                'family' => 'ویرایش',
                'mobile' => '09120000003',
                'email' => 'student-edited@sharifi.test',
                'gender' => 'female',
            ])
            ->assertRedirect();

        $student->refresh();
        $this->assertSame('student-edited@sharifi.test', $student->email);
    }

    public function test_student_can_update_extended_profile(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = User::where('mobile', '09120000003')->firstOrFail();
        $city = ProvinceCity::query()->whereNotNull('parent_id')->where('title', 'تهران')->firstOrFail();

        $this->actingAs($student)
            ->put('/profile', [
                'name' => $student->name,
                'family' => $student->family,
                'mobile' => $student->mobile,
                'email' => $student->email,
                'gender' => $student->gender,
                'headline' => 'دانشجوی فعال',
                'bio' => 'علاقه‌مند به لاراول',
                'birth_date' => '2000-01-15',
                'national_code' => '0499370899',
                'province_city_id' => $city->id,
                'is_public' => false,
            ])
            ->assertRedirect();

        $student->load('profile');
        $this->assertNotNull($student->profile);
        $this->assertSame('دانشجوی فعال', $student->profile->headline);
        $this->assertSame('0499370899', $student->profile->national_code);
        $this->assertFalse($student->profile->is_public);
    }
}
