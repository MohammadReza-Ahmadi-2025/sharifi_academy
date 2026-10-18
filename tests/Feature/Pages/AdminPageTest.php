<?php

namespace Tests\Feature\Pages;

use App\Models\User;
use App\Modules\Pages\Models\Page;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_page(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/pages', [
                'title' => 'قوانین',
                'slug' => 'terms',
                'content' => '<p>قوانین سایت</p>',
                'is_active' => true,
                'meta_title' => 'قوانین',
            ])
            ->assertRedirect(route('admin.pages.index'));

        $this->assertDatabaseHas('pages', [
            'title' => 'قوانین',
            'slug' => 'terms',
            'is_active' => true,
        ]);
    }

    public function test_admin_pages_index(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();

        Page::query()->create([
            'title' => 'تماس',
            'slug' => 'contact',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin/pages')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Pages/Index'));
    }
}
