<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Category;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Pages\Models\Page;
use App\Modules\Profile\Support\ResumeModeration;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_includes_public_urls_and_excludes_private_ones(): void
    {
        $this->seed(DatabaseSeeder::class);
        Role::findOrCreate('instructor');

        $publishedCourse = Course::query()->create([
            'title' => 'دوره عمومی سایت‌مپ',
            'slug' => 'sitemap-public-course',
            'status' => CourseStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        Course::query()->create([
            'title' => 'دوره پیش‌نویس',
            'slug' => 'sitemap-draft-course',
            'status' => CourseStatus::Draft,
        ]);

        $courseCategory = Category::query()->create([
            'type' => Category::TYPE_COURSE,
            'title' => 'دسته دوره سایت‌مپ',
            'slug' => 'sitemap-course-cat',
            'status' => true,
            'parent_id' => null,
            'sort' => 0,
        ]);

        Category::query()->create([
            'type' => Category::TYPE_COURSE,
            'title' => 'دسته غیرفعال',
            'slug' => 'sitemap-inactive-cat',
            'status' => false,
            'parent_id' => null,
            'sort' => 1,
        ]);

        $author = User::where('mobile', '09120000001')->firstOrFail();

        $publishedPost = Post::query()->create([
            'author_id' => $author->id,
            'title' => 'مقاله عمومی سایت‌مپ',
            'slug' => 'sitemap-public-post',
            'status' => PostStatus::Published,
            'published_at' => now()->subHour(),
        ]);

        Post::query()->create([
            'author_id' => $author->id,
            'title' => 'مقاله پیش‌نویس',
            'slug' => 'sitemap-draft-post',
            'status' => PostStatus::Draft,
        ]);

        $activePage = Page::query()->create([
            'title' => 'صفحه فعال سایت‌مپ',
            'slug' => 'sitemap-active-page',
            'content' => '<p>متن</p>',
            'is_active' => true,
        ]);

        Page::query()->create([
            'title' => 'صفحه غیرفعال',
            'slug' => 'sitemap-inactive-page',
            'content' => null,
            'is_active' => false,
        ]);

        $instructor = User::factory()->create([
            'name' => 'استاد',
            'family' => 'سایت‌مپ',
            'status' => 1,
        ]);
        $instructor->assignRole('instructor');
        ResumeModeration::markApproved($instructor);

        $hiddenInstructor = User::factory()->create([
            'name' => 'استاد',
            'family' => 'مخفی',
            'status' => 1,
        ]);
        $hiddenInstructor->assignRole('instructor');

        $response = $this->get('/sitemap.xml');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $body = $response->getContent();

        $this->assertStringContainsString('<urlset', $body);
        $this->assertStringContainsString(route('home'), $body);
        $this->assertStringContainsString(route('blog.index'), $body);
        $this->assertStringContainsString('/courses/'.$publishedCourse->publicSlug(), $body);
        $this->assertStringContainsString('/categories/'.$courseCategory->publicSlug(), $body);
        $this->assertStringContainsString('/blog/'.$publishedPost->publicSlug(), $body);
        $this->assertStringContainsString(route('pages.show', $activePage), $body);
        $this->assertStringContainsString(route('instructors.show', $instructor), $body);

        $this->assertStringNotContainsString('sitemap-draft-course', $body);
        $this->assertStringNotContainsString('sitemap-inactive-cat', $body);
        $this->assertStringNotContainsString('sitemap-draft-post', $body);
        $this->assertStringNotContainsString('sitemap-inactive-page', $body);
        $this->assertStringNotContainsString(route('instructors.show', $hiddenInstructor), $body);
        $this->assertDoesNotMatchRegularExpression('#https?://[^<]+/(admin|instructor|student)(/|<)#', $body);

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Sitemap: '.route('sitemap'), false)
            ->assertSee('Disallow: /admin', false)
            ->assertSee('Disallow: /search', false);
    }
}
