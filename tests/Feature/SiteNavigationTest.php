<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalog\Models\Category;
use App\Support\SiteNavigation;
use Database\Seeders\CourseDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_public_and_auth_links(): void
    {
        $nav = SiteNavigation::forUser(null);

        $labels = collect($nav['items'])->pluck('label')->all();

        $this->assertContains('خانه', $labels);
        $this->assertContains('دوره‌ها', $labels);
        $this->assertContains('ورود / ثبت‌نام', $labels);
        $this->assertNotContains('حساب من', $labels);
        $this->assertNull($nav['cta']);

        $courses = collect($nav['items'])->firstWhere('id', 'courses');
        $this->assertSame('mega', $courses['type']);
        $this->assertNotEmpty($courses['mega']['columns']);
    }

    public function test_course_menu_includes_database_categories_marked_for_menu(): void
    {
        $this->seed(CourseDemoSeeder::class);

        Category::query()
            ->where('slug', 'programming')
            ->update(['show_in_menu' => true]);

        $nav = SiteNavigation::forUser(null);
        $courses = collect($nav['items'])->firstWhere('id', 'courses');

        $titles = collect($courses['mega']['columns'])->pluck('title')->all();
        $this->assertContains('برنامه‌نویسی', $titles);
    }

    public function test_authenticated_user_sees_account_instead_of_login(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $nav = SiteNavigation::forUser($user);
        $labels = collect($nav['items'])->pluck('label')->all();

        $this->assertContains('حساب من', $labels);
        $this->assertNotContains('ورود / ثبت‌نام', $labels);
    }

    public function test_navigation_and_menu_categories_are_shared_with_inertia(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('navigation.items')
            ->has('menuCategories')
            ->where('navigation.cta', null)
        );
    }
}
