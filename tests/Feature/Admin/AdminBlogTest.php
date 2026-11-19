<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Category;
use App\Modules\Settings\Models\SiteSetting;
use App\Modules\Settings\Services\SiteSettingsService;
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

    public function test_admin_can_update_blog_seo_settings(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/posts/settings')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Blog/Settings')
                ->has('settings.blogs_meta_title'));

        $this->actingAs($admin)
            ->put('/admin/posts/settings', [
                'blogs_meta_title' => 'وبلاگ تست | سئو',
                'blogs_meta_description' => 'توضیح متای صفحه لیست پست‌ها',
                'blogs_meta_keywords' => 'وبلاگ, آموزش',
                'blogs_header_title' => 'مطالب آموزشی',
                'blogs_subheader_title' => 'جدیدترین مقالات',
            ])
            ->assertRedirect(route('admin.posts.settings.edit'));

        $this->assertSame(
            'وبلاگ تست | سئو',
            SiteSetting::query()->where('key', 'blogs_meta_title')->value('value'),
        );

        app(SiteSettingsService::class)->forgetCache();

        $this->assertSame(
            'وبلاگ تست | سئو',
            app(SiteSettingsService::class)->get('blogs_meta_title'),
        );
    }

    public function test_admin_can_update_community_posts_seo_settings(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/posts/feed-settings')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Blog/CommunityPostsSettings')
                ->has('settings.posts_meta_title'));

        $this->actingAs($admin)
            ->put('/admin/posts/feed-settings', [
                'posts_meta_title' => 'پست‌ها تست | سئو',
                'posts_meta_description' => 'توضیح متای فید پست‌ها',
                'posts_meta_keywords' => 'پست, کاربران',
                'posts_header_title' => 'انجمن پست‌ها',
                'posts_subheader_title' => 'آخرین نوشته‌های کاربران',
            ])
            ->assertRedirect(route('admin.posts.feed-settings.edit'));

        app(SiteSettingsService::class)->forgetCache();

        $this->assertSame(
            'پست‌ها تست | سئو',
            app(SiteSettingsService::class)->get('posts_meta_title'),
        );

        $this->get('/posts')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Posts/Index')
                ->where('page.header_title', 'انجمن پست‌ها')
                ->where('seo.title', 'پست‌ها تست | سئو'));
    }
}
