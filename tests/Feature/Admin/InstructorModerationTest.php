<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Catalog\Models\Category;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Profile\Enums\ResumeModerationStatus;
use App\Modules\Profile\Models\Resume;
use App\Modules\Profile\Support\ResumeModeration;
use App\Modules\Roles\Support\DefaultRolePermissions;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class InstructorModerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        DefaultRolePermissions::sync();
    }

    public function test_instructor_publish_request_creates_pending_review_course(): void
    {
        $instructor = User::where('mobile', '09120000002')->firstOrFail();
        $category = Category::query()->create([
            'title' => 'دوره تست',
            'slug' => 'mod-course-cat',
            'type' => Category::TYPE_COURSE,
            'status' => 1,
        ]);

        $this->actingAs($instructor)->post('/instructor/courses', [
            'title' => 'دوره تست تایید',
            'slug' => 'pending-course',
            'status' => CourseStatus::Published->value,
            'category_ids' => [$category->id],
            'gallery' => [
                0 => ['file' => UploadedFile::fake()->image('course.jpg')],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('courses', [
            'title' => 'دوره تست تایید',
            'status' => CourseStatus::PendingReview->value,
            'created_by' => $instructor->id,
        ]);
    }

    public function test_admin_can_approve_pending_course(): void
    {
        $instructor = User::where('mobile', '09120000002')->firstOrFail();
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $course = Course::query()->create([
            'title' => 'دوره صف',
            'slug' => 'queue-course',
            'status' => CourseStatus::PendingReview,
            'created_by' => $instructor->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.instructor-moderation.courses.approve', $course))
            ->assertRedirect();

        $course->refresh();
        $this->assertSame(CourseStatus::Published, $course->status);
    }

    public function test_resume_submit_and_admin_approve_makes_profile_public(): void
    {
        $instructor = User::where('mobile', '09120000002')->firstOrFail();
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        Resume::query()->create([
            'user_id' => $instructor->id,
            'type' => 'experience',
            'title' => 'مدرس',
            'display_order' => 0,
        ]);

        $this->actingAs($instructor)
            ->post(route('instructor.resume.submit'))
            ->assertRedirect(route('instructor.resume.index'));

        $this->assertSame(ResumeModerationStatus::Pending, ResumeModeration::status($instructor->fresh()));

        $this->get(route('instructors.show', $instructor))->assertNotFound();

        $this->actingAs($admin)
            ->post(route('admin.instructor-moderation.resumes.approve', $instructor))
            ->assertRedirect();

        $this->assertSame(ResumeModerationStatus::Approved, ResumeModeration::status($instructor->fresh()));

        $this->get(route('instructors.show', $instructor))->assertOk();
    }

    public function test_admin_moderation_index_requires_permission(): void
    {
        $instructor = User::where('mobile', '09120000002')->firstOrFail();

        $this->actingAs($instructor)
            ->get(route('admin.instructor-moderation.index'))
            ->assertForbidden();
    }
}
