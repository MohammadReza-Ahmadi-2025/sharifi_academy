<?php

namespace Tests\Feature\Blog;

use App\Models\User;
use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Category;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBlogCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_category_page_lists_published_posts(): void
    {
        $this->seed(DatabaseSeeder::class);

        $author = User::where('mobile', '09120000001')->firstOrFail();

        $category = Category::query()->create([
            'type' => Category::TYPE_BLOG,
            'title' => 'دسته وبلاگ تست',
            'slug' => 'blog-cat-test',
            'status' => true,
            'parent_id' => null,
            'sort' => 0,
        ]);

        $post = Post::query()->create([
            'author_id' => $author->id,
            'title' => 'مقاله در دسته',
            'slug' => 'post-in-blog-cat',
            'status' => PostStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $category->posts()->attach($post->id);

        $this->get('/blog/categories/'.$category->slug)
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Blog/CategoryShow')
                ->where('category.title', 'دسته وبلاگ تست')
                ->has('structuredData.breadcrumb')
                ->has('posts.data', 1)
            );
    }

    public function test_course_category_url_does_not_show_blog_category_page(): void
    {
        $this->seed(DatabaseSeeder::class);

        $category = Category::query()->create([
            'type' => Category::TYPE_COURSE,
            'title' => 'دسته دوره',
            'slug' => 'course-only-cat',
            'status' => true,
            'parent_id' => null,
            'sort' => 0,
        ]);

        $this->get('/blog/categories/'.$category->slug)->assertNotFound();
    }
}
