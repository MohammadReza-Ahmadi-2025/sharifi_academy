<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Category;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminBlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_manage_blog_category_and_post(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/categories', [
                'type' => Category::TYPE_BLOG,
                'title' => 'آموزش',
                'status' => true,
                'image' => [
                    0 => ['file' => UploadedFile::fake()->image('blog-cat.jpg')],
                ],
            ])
            ->assertRedirect();

        $category = Category::query()->where('title', 'آموزش')->firstOrFail();
        $this->assertSame(Category::TYPE_BLOG, $category->type);

        $this->actingAs($admin)
            ->post('/admin/posts', [
                'title' => 'معرفی دوره',
                'content' => 'متن کامل پست',
                'status' => 'published',
                'category_ids' => [$category->id],
                'featured' => [
                    0 => ['file' => UploadedFile::fake()->image('featured.jpg')],
                ],
            ])
            ->assertRedirect();

        $post = Post::query()->where('title', 'معرفی دوره')->firstOrFail();
        $this->assertSame($admin->id, $post->author_id);
        $this->assertTrue($post->categories()->whereKey($category->id)->exists());
        $this->assertNotNull($post->featuredImage);

        $this->actingAs($admin)
            ->get('/admin/posts/'.$post->id.'/edit')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Blog/Posts/Edit'));
    }
}
