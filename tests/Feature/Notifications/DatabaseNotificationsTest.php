<?php

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Modules\Catalog\Models\Category;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Profile\Support\ResumeModeration;
use App\Modules\Roles\Support\DefaultRolePermissions;
use App\Support\Notifications\Messages\CourseModerationResultNotification;
use App\Support\Notifications\Messages\CourseSubmittedForReviewNotification;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DatabaseNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        DefaultRolePermissions::sync();
    }

    public function test_instructor_course_submission_notifies_moderators(): void
    {
        Notification::fake();

        $instructor = User::where('mobile', '09120000002')->firstOrFail();
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $category = Category::query()->create([
            'title' => 'دسته نوتیف',
            'slug' => 'notif-cat',
            'type' => Category::TYPE_COURSE,
            'status' => 1,
        ]);

        $course = Course::query()->create([
            'title' => 'Notify course',
            'slug' => 'notify-course',
            'status' => CourseStatus::Draft,
            'created_by' => $instructor->id,
        ]);
        $course->categories()->attach($category->id);

        $response = $this->actingAs($instructor)
            ->put("/instructor/courses/{$course->id}", [
                'title' => 'Notify course updated',
                'slug' => 'notify-course',
                'status' => CourseStatus::Published->value,
                'category_ids' => [$category->id],
                'gallery' => [
                    0 => ['file' => UploadedFile::fake()->image('course.jpg')],
                ],
            ]);

        $response->assertRedirect();

        $course->refresh();
        $this->assertSame(CourseStatus::PendingReview, $course->status);

        Notification::assertSentTo($admin, CourseSubmittedForReviewNotification::class);
    }

    public function test_course_approval_notifies_instructor(): void
    {
        $instructor = User::where('mobile', '09120000002')->firstOrFail();
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $course = Course::query()->create([
            'title' => 'Approve me',
            'slug' => 'approve-me',
            'status' => CourseStatus::PendingReview,
            'created_by' => $instructor->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.instructor-moderation.courses.approve', $course));

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $instructor->id,
            'type' => CourseModerationResultNotification::class,
        ]);
    }

    public function test_resume_submit_notifies_admin(): void
    {
        Notification::fake();

        $instructor = User::where('mobile', '09120000002')->firstOrFail();
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $instructor->resumes()->create([
            'type' => 'experience',
            'title' => 'تدریس',
            'display_order' => 0,
        ]);

        $this->actingAs($instructor)->post('/instructor/resume/submit');

        Notification::assertSentTo($admin, \App\Support\Notifications\Messages\ResumeSubmittedForReviewNotification::class);
    }
}
