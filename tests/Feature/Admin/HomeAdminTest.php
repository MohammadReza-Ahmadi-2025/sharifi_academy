<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Home\Models\HomeSection;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_manage_home_sections_and_banners(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/home-sections')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Home/Sections/Index'));

        $this->actingAs($admin)
            ->get('/admin/home-sections/create')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Home/Sections/Create'));

        $this->actingAs($admin)
            ->get('/admin/home-banners')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Home/Banners/Index'));

        $section = HomeSection::query()->firstOrFail();

        $this->actingAs($admin)
            ->get("/admin/home-sections/{$section->id}/edit")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Home/Sections/Edit')
                ->has('section.tabs')
            );
    }
}
