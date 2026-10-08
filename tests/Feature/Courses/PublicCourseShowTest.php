<?php

namespace Tests\Feature\Courses;

use App\Models\User;
use App\Modules\Comments\Enums\CommentStatus;
use App\Modules\Comments\Models\Comment;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCourseShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_course_show_page_renders(): void
    {
        $course = Course::query()->create([
            'title' => 'دوره تست',
            'slug' => 'test-course',
            'status' => CourseStatus::Published,
            'short_description' => 'خلاصه دوره',
            'description' => 'توضیحات کامل',
            'published_at' => now(),
        ]);

        $this->get(route('courses.show', $course))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/CourseShow')
                ->has('course', fn ($coursePage) => $coursePage
                    ->where('slug', 'test-course')
                    ->where('title', 'دوره تست')
                    ->has('seo')
                    ->has('comments')
                    ->etc()
                )
                ->has('structuredData.breadcrumb')
                ->has('structuredData.course')
                ->has('structuredData.webPage'));
    }

    public function test_draft_course_is_not_public(): void
    {
        $course = Course::query()->create([
            'title' => 'پیش‌نویس',
            'slug' => 'draft-course',
            'status' => CourseStatus::Draft,
        ]);

        $this->get(route('courses.show', $course))->assertNotFound();
    }

    public function test_authenticated_user_can_submit_course_comment(): void
    {
        $user = User::factory()->create();
        $course = Course::query()->create([
            'title' => 'دوره نظر',
            'slug' => 'comment-course',
            'status' => CourseStatus::Published,
            'published_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('courses.comments.store', $course), [
                'body' => 'دوره عالی بود',
                'rating' => 5,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'commentable_type' => $course->getMorphClass(),
            'commentable_id' => $course->id,
            'user_id' => $user->id,
            'body' => 'دوره عالی بود',
            'status' => CommentStatus::Pending->value,
        ]);
    }

    public function test_approved_comments_appear_on_course_page(): void
    {
        $course = Course::query()->create([
            'title' => 'دوره با نظر',
            'slug' => 'with-comments',
            'status' => CourseStatus::Published,
            'published_at' => now(),
        ]);

        Comment::query()->create([
            'commentable_type' => $course->getMorphClass(),
            'commentable_id' => $course->id,
            'body' => 'نظر تأیید شده',
            'status' => CommentStatus::Approved,
        ]);

        $this->get(route('courses.show', $course))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('course.comments', 1)
                ->where('course.comments.0.body', 'نظر تأیید شده'));
    }
}
