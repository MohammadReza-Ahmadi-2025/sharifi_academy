<?php

namespace Tests\Feature\Catalog;

use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_slug_with_spaces_redirects_to_canonical_hyphenated_url(): void
    {
        $category = Category::query()->create([
            'type' => Category::TYPE_COURSE,
            'title' => 'Training in Mechanical Engineering Software',
            'slug' => 'Training in Mechanical Engineering Software',
            'status' => true,
            'sort' => 1,
        ]);

        $response = $this->get('/categories/Training%20in%20Mechanical%20Engineering%20Software');
        $response->assertRedirect(route('categories.show', ['category' => 'training-in-mechanical-engineering-software']));
    }

    public function test_course_category_show_lists_published_courses(): void
    {
        $category = Category::query()->create([
            'type' => Category::TYPE_COURSE,
            'title' => 'مهندسی',
            'slug' => 'engineering',
            'status' => true,
            'sort' => 1,
        ]);

        $published = Course::query()->create([
            'title' => 'دوره منتشر',
            'slug' => 'published-course',
            'status' => CourseStatus::Published,
            'published_at' => now(),
        ]);

        $draft = Course::query()->create([
            'title' => 'پیش‌نویس',
            'slug' => 'draft-course',
            'status' => CourseStatus::Draft,
        ]);

        $category->courses()->attach([$published->id, $draft->id]);

        $this->get(route('categories.show', $category))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Categories/Show')
                ->where('category.slug', 'engineering')
                ->where('listing', 'courses')
                ->has('items.data', 1)
                ->where('items.data.0.slug', 'published-course')
                ->has('filters')
                ->has('structuredData.breadcrumb')
                ->where('structuredData.webPage.@type', 'CollectionPage')
                ->has('structuredData.itemList'));
    }

    public function test_product_category_show_lists_visible_products(): void
    {
        $category = Category::query()->create([
            'type' => Category::TYPE_PRODUCT,
            'title' => 'سیم و کابل',
            'slug' => 'wire-cable',
            'status' => true,
            'sort' => 1,
        ]);

        $visible = Product::query()->create([
            'title' => 'کابل شبکه',
            'slug' => 'network-cable',
            'status' => true,
            'is_visible' => true,
            'min_effective_price' => 100_000,
        ]);

        $hidden = Product::query()->create([
            'title' => 'مخفی',
            'slug' => 'hidden-product',
            'status' => true,
            'is_visible' => false,
        ]);

        $category->products()->attach([$visible->id, $hidden->id]);

        $this->get(route('categories.show', $category))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Categories/Show')
                ->where('listing', 'products')
                ->has('items.data', 1)
                ->where('items.data.0.slug', 'network-cable'));
    }

    public function test_category_list_url_redirects_home(): void
    {
        $this->get('/categories?type=course')->assertRedirect('/');
        $this->get('/courses/categories')->assertRedirect('/');
    }

    public function test_legacy_course_category_urls_redirect(): void
    {
        $category = Category::query()->create([
            'type' => Category::TYPE_COURSE,
            'title' => 'برنامه‌نویسی',
            'slug' => 'programming',
            'status' => true,
        ]);

        $this->get('/courses/categories/'.$category->slug)
            ->assertRedirect(route('categories.show', $category));
    }
}
