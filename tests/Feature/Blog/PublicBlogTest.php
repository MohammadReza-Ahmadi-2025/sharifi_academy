<?php

namespace Tests\Feature\Blog;

use App\Models\User;
use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Models\Post;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_index_renders_with_seo_and_structured_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get('/blog')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Blog/Index')
                ->has('seo.canonical')
                ->has('structuredData.breadcrumb')
                ->has('structuredData.itemList')
            );
    }

    public function test_published_post_show_page(): void
    {
        $this->seed(DatabaseSeeder::class);

        $author = User::where('mobile', '09120000001')->firstOrFail();

        $post = Post::query()->create([
            'author_id' => $author->id,
            'title' => 'مقاله تست عمومی',
            'slug' => 'public-test-post',
            'excerpt' => '<p>چکیده <strong>HTML</strong></p>',
            'content' => '<p>محتوای کامل مقاله</p>',
            'status' => PostStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $this->get('/blog/'.$post->id.'/'.$post->slug)
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Blog/Show')
                ->where('post.title', 'مقاله تست عمومی')
                ->has('post.seo.canonical')
                ->has('structuredData.breadcrumb')
                ->has('structuredData.blogPosting')
            );
    }

    public function test_legacy_blog_slug_url_redirects_to_id_slug(): void
    {
        $this->seed(DatabaseSeeder::class);

        $post = Post::query()->create([
            'author_id' => User::where('mobile', '09120000001')->firstOrFail()->id,
            'title' => 'مقاله قدیمی',
            'slug' => 'legacy-post-slug',
            'status' => PostStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $this->get('/blog/'.$post->slug)
            ->assertRedirect('/blog/'.$post->id.'/'.$post->slug);
    }

    public function test_wrong_blog_slug_with_valid_id_redirects_to_canonical(): void
    {
        $this->seed(DatabaseSeeder::class);

        $post = Post::query()->create([
            'author_id' => User::where('mobile', '09120000001')->firstOrFail()->id,
            'title' => 'مقاله ریدایرکت',
            'slug' => 'canonical-post-slug',
            'status' => PostStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $this->get('/blog/'.$post->id.'/old-outdated-slug')
            ->assertRedirect('/blog/'.$post->id.'/canonical-post-slug');
    }

    public function test_draft_post_is_not_public(): void
    {
        $this->seed(DatabaseSeeder::class);

        $post = Post::query()->create([
            'title' => 'پیش‌نویس',
            'slug' => 'draft-post',
            'status' => PostStatus::Draft,
        ]);

        $this->get('/blog/'.$post->id.'/'.$post->slug)->assertNotFound();
    }
}
