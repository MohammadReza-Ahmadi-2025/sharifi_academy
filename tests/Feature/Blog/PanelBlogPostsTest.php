<?php

namespace Tests\Feature\Blog;

use App\Models\User;
use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Category;
use App\Modules\Roles\Support\DefaultRolePermissions;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PanelBlogPostsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        DefaultRolePermissions::sync();
    }

    public function test_student_can_create_pending_post_without_publishing(): void
    {
        $student = User::where('mobile', '09120000003')->firstOrFail();
        $category = Category::query()->create([
            'type' => Category::TYPE_BLOG,
            'title' => 'دانشجویی',
            'slug' => 'student-blog-cat',
            'status' => true,
        ]);

        $this->actingAs($student)
            ->get('/student/posts')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Student/Blog/Posts/Index'));

        $this->actingAs($student)
            ->post('/student/posts', [
                'title' => 'پست دانشجو',
                'content' => "متن ساده بدون ادیتور\nخط دوم",
                'status' => PostStatus::PendingReview->value,
                'category_ids' => [$category->id],
                'featured' => [
                    0 => ['file' => UploadedFile::fake()->image('s.jpg')],
                ],
            ])
            ->assertRedirect();

        $post = Post::query()->where('title', 'پست دانشجو')->firstOrFail();
        $this->assertSame($student->id, $post->author_id);
        $this->assertSame(PostStatus::PendingReview, $post->status);
        $this->assertNull($post->published_at);

        $this->get('/blog/'.$post->slug)->assertNotFound();

        $admin = User::where('mobile', '09120000001')->firstOrFail();
        $this->actingAs($admin)
            ->put('/admin/posts/'.$post->id, [
                'title' => $post->title,
                'content' => $post->content,
                'status' => PostStatus::Published->value,
                'category_ids' => [$category->id],
                'featured' => [
                    0 => ['id' => $post->featuredImage?->id],
                ],
            ])
            ->assertRedirect();

        $this->assertSame(PostStatus::Published, $post->fresh()->status);
        $this->get('/blog/'.$post->slug)->assertOk();
    }

    public function test_instructor_can_manage_own_posts_only(): void
    {
        $instructor = User::where('mobile', '09120000002')->firstOrFail();
        $other = User::where('mobile', '09120000003')->firstOrFail();
        $category = Category::query()->create([
            'type' => Category::TYPE_BLOG,
            'title' => 'استادی',
            'slug' => 'instructor-blog-cat',
            'status' => true,
        ]);

        $foreign = Post::query()->create([
            'author_id' => $other->id,
            'title' => 'پست دیگری',
            'slug' => 'foreign-post',
            'content' => 'x',
            'status' => PostStatus::Draft,
        ]);

        $this->actingAs($instructor)
            ->get('/instructor/posts/'.$foreign->id.'/edit')
            ->assertNotFound();

        $this->actingAs($instructor)
            ->post('/instructor/posts', [
                'title' => 'پست استاد',
                'content' => 'محتوا',
                'status' => PostStatus::Draft->value,
                'category_ids' => [$category->id],
                'featured' => [
                    0 => ['file' => UploadedFile::fake()->image('i.jpg')],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('posts', [
            'title' => 'پست استاد',
            'author_id' => $instructor->id,
            'status' => PostStatus::Draft->value,
        ]);
    }

    public function test_panel_author_cannot_set_published_status(): void
    {
        $student = User::where('mobile', '09120000003')->firstOrFail();
        $category = Category::query()->create([
            'type' => Category::TYPE_BLOG,
            'title' => 'محدود',
            'slug' => 'limited-blog-cat',
            'status' => true,
        ]);

        $this->actingAs($student)
            ->post('/student/posts', [
                'title' => 'تلاش انتشار مستقیم',
                'content' => 'متن',
                'status' => PostStatus::Published->value,
                'category_ids' => [$category->id],
                'featured' => [
                    0 => ['file' => UploadedFile::fake()->image('p.jpg')],
                ],
            ])
            ->assertSessionHasErrors('status');
    }
}
