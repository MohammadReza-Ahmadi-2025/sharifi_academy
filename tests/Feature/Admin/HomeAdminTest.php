<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Models\User;
use App\Modules\Home\Models\Banner;
use App\Modules\Home\Models\HomeSection;
use App\Services\Home\HomePageService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HomeAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_manage_home_sections(): void
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

        $section = HomeSection::query()->firstOrFail();

        $this->actingAs($admin)
            ->get("/admin/home-sections/{$section->id}/edit")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Home/Sections/Edit')
                ->has('section.tabs')
            );
    }

    public function test_super_admin_can_create_home_section_with_banner_slide_image(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $response = $this->actingAs($admin)->post('/admin/home-sections', [
            'title' => 'سکشن بنر تست',
            'status' => true,
            'settings' => [
                'section_template' => 'banners',
            ],
            'tabs' => [
                [
                    'title' => 'اسلایدها',
                    'content_type' => 'banner',
                    'source_type' => 'manual',
                    'is_active' => true,
                    'sort_order' => 0,
                    'banners' => [
                        [
                            'title' => 'اسلاید ۱',
                            'image' => [
                                0 => ['file' => UploadedFile::fake()->image('slide.jpg')],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect();

        $section = HomeSection::query()->where('title', 'سکشن بنر تست')->first();
        $this->assertNotNull($section);

        $banner = Banner::query()
            ->where('title', 'اسلاید ۱')
            ->whereHas('file')
            ->first();

        $this->assertNotNull($banner);
    }

    public function test_reorder_home_sections_updates_sort_and_flushes_home_cache(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $sections = HomeSection::query()->orderBy('sort')->get();
        $this->assertGreaterThanOrEqual(2, $sections->count());

        $first = $sections->first();
        $second = $sections->skip(1)->first();

        Cache::put('site:home:payload', ['cached' => true], 60);

        $this->actingAs($admin)
            ->put('/admin/home-sections/reorder', [
                'sections' => [
                    ['id' => $second->id, 'sort' => 5],
                    ['id' => $first->id, 'sort' => 15],
                ],
            ])
            ->assertRedirect();

        $this->assertSame(5, $second->fresh()->sort);
        $this->assertSame(15, $first->fresh()->sort);
        $this->assertFalse(Cache::has('site:home:payload'));
        HomePageService::flush();
    }

    public function test_home_section_changes_are_recorded_in_activity_log(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();
        $section = HomeSection::query()->firstOrFail();

        $this->actingAs($admin);
        $section->update(['title' => 'سکشن تست فعالیت']);

        $log = ActivityLog::query()
            ->where('subject_type', HomeSection::class)
            ->where('subject_id', $section->id)
            ->where('action', 'updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('changes', $log->properties);
        $this->assertSame('سکشن تست فعالیت', $log->properties['changes']['title']['to'] ?? null);
    }
}
