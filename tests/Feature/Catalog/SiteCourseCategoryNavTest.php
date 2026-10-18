<?php

namespace Tests\Feature\Catalog;

use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Services\SiteCourseCategoryNavService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SiteCourseCategoryNavTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_category_nav_tree_is_shared_with_inertia(): void
    {
        $parent = Category::query()->create([
            'title' => 'برنامه‌نویسی',
            'slug' => 'programming',
            'type' => Category::TYPE_COURSE,
            'status' => 1,
            'sort' => 1,
        ]);

        Category::query()->create([
            'parent_id' => $parent->id,
            'title' => 'لاراول',
            'slug' => 'laravel',
            'type' => Category::TYPE_COURSE,
            'status' => 1,
            'sort' => 1,
        ]);

        SiteCourseCategoryNavService::flush();

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('courseCategoryNav', 1)
                ->where('courseCategoryNav.0.slug', 'programming')
                ->has('courseCategoryNav.0.children', 1)
                ->where('courseCategoryNav.0.children.0.slug', 'laravel')
            );
    }

    public function test_empty_cache_is_rebuilt_when_course_categories_exist(): void
    {
        Category::query()->create([
            'title' => 'دوره‌ها',
            'slug' => 'courses-root',
            'type' => Category::TYPE_COURSE,
            'status' => 1,
            'sort' => 1,
        ]);

        Cache::put(SiteCourseCategoryNavService::CACHE_KEY, [], 600);

        $tree = app(SiteCourseCategoryNavService::class)->tree();

        $this->assertCount(1, $tree);
        $this->assertSame('courses-root', $tree[0]['slug']);
    }

    public function test_inactive_course_categories_are_excluded(): void
    {
        Category::query()->create([
            'title' => 'غیرفعال',
            'slug' => 'inactive',
            'type' => Category::TYPE_COURSE,
            'status' => 0,
            'sort' => 1,
        ]);

        SiteCourseCategoryNavService::flush();

        $tree = app(SiteCourseCategoryNavService::class)->tree();

        $this->assertSame([], $tree);
    }
}
