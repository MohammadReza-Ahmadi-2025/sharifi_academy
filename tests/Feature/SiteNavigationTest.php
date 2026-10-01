<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\SiteNavigation;
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

    public function test_authenticated_user_sees_account_instead_of_login(): void
    {
        $user = User::factory()->create();

        $nav = SiteNavigation::forUser($user);
        $labels = collect($nav['items'])->pluck('label')->all();

        $this->assertContains('حساب من', $labels);
        $this->assertNotContains('ورود / ثبت‌نام', $labels);
    }

    public function test_navigation_is_shared_with_inertia(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('navigation.items')
            ->where('navigation.cta', null)
        );
    }
}
