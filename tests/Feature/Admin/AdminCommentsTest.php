<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Blog\Enums\PostKind;
use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Category;
use App\Modules\Comments\Enums\CommentStatus;
use App\Modules\Comments\Models\Comment;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\Course;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCommentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_moderate_and_reply_to_comment(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $category = Category::query()->create([
            'type' => Category::TYPE_BLOG,
            'title' => 'اخبار',
            'slug' => 'news',
            'status' => 1,
            'sort' => 1,
        ]);

        $post = Post::query()->create([
            'author_id' => $admin->id,
            'title' => 'اولین پست',
            'slug' => 'first-post',
            'content' => 'متن',
            'status' => 'draft',
            'kind' => PostKind::Community,
        ]);

        $post->categories()->attach($category->id);

        $comment = Comment::query()->create([
            'commentable_type' => Post::class,
            'commentable_id' => $post->id,
            'body' => 'نظر تست',
            'status' => CommentStatus::Pending,
            'commenter_type' => 'user',
            'display_name' => 'کاربر تست',
        ]);

        $this->actingAs($admin)
            ->post('/admin/comments/'.$comment->id.'/approve')
            ->assertRedirect();

        $comment->refresh();
        $this->assertSame(CommentStatus::Approved, $comment->status);

        $this->actingAs($admin)
            ->post('/admin/comments/'.$comment->id.'/reply', [
                'body' => 'پاسخ ادمین',
            ])
            ->assertRedirect();

        $this->assertTrue(
            Comment::query()->where('parent_id', $comment->id)->where('body', 'پاسخ ادمین')->exists()
        );

        $this->actingAs($admin)
            ->get('/admin/comments')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Comments/Index')
                ->where('comments.data.0.commentable_type_label', 'پست کاربری')
                ->where('comments.data.0.commentable_title', 'اولین پست')
                ->where('comments.data.0.commentable_url', '/posts/'.$post->id.'/first-post')
                ->has('comments.data.0.children'));
    }

    public function test_admin_comments_index_shows_course_target(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $course = Course::query()->create([
            'title' => 'دوره تست نظرات',
            'slug' => 'course-comments-target',
            'status' => CourseStatus::Published,
            'instructor_id' => $admin->id,
        ]);

        Comment::query()->create([
            'commentable_type' => Course::class,
            'commentable_id' => $course->id,
            'body' => 'نظر دوره',
            'status' => CommentStatus::Pending,
            'commenter_type' => 'user',
            'display_name' => 'دانشجو',
        ]);

        $this->actingAs($admin)
            ->get('/admin/comments')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Comments/Index')
                ->where('comments.data.0.commentable_type_label', 'دوره')
                ->where('comments.data.0.commentable_title', 'دوره تست نظرات')
                ->where('comments.data.0.commentable_url', '/courses/'.$course->id.'/course-comments-target'));
    }
}
