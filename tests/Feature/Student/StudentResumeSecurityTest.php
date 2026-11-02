<?php

namespace Tests\Feature\Student;

use App\Models\User;
use App\Modules\Profile\Enums\ResumeModerationStatus;
use App\Modules\Profile\Models\Resume;
use App\Modules\Profile\Support\ResumeModeration;
use App\Modules\Roles\Support\DefaultRolePermissions;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentResumeSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        DefaultRolePermissions::sync();
    }

    public function test_student_cannot_update_another_students_resume(): void
    {
        $student = User::where('mobile', '09120000003')->firstOrFail();
        $other = User::factory()->create([
            'mobile' => '09121112233',
            'status' => 1,
        ]);
        $other->assignRole('student');

        $foreign = Resume::query()->create([
            'user_id' => $other->id,
            'type' => 'education',
            'title' => 'رزومه دیگری',
            'display_order' => 0,
        ]);

        $this->actingAs($student)
            ->put('/student/resume/'.$foreign->id, [
                'type' => 'education',
                'title' => 'هک شده',
                'is_current' => false,
            ])
            ->assertNotFound();

        $this->assertSame('رزومه دیگری', $foreign->fresh()->title);
    }

    public function test_student_cannot_open_instructor_resume_panel(): void
    {
        $student = User::where('mobile', '09120000003')->firstOrFail();

        $this->actingAs($student)
            ->get('/instructor/resume')
            ->assertNotFound();
    }

    public function test_instructor_cannot_open_student_resume_panel(): void
    {
        $instructor = User::where('mobile', '09120000002')->firstOrFail();

        $this->actingAs($instructor)
            ->get('/student/resume')
            ->assertNotFound();
    }

    public function test_admin_cannot_approve_draft_resume(): void
    {
        $student = User::where('mobile', '09120000003')->firstOrFail();
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        Resume::query()->create([
            'user_id' => $student->id,
            'type' => 'education',
            'title' => 'پیش‌نویس',
            'display_order' => 0,
        ]);

        $this->assertSame(ResumeModerationStatus::Draft, ResumeModeration::status($student->fresh()));

        $this->actingAs($admin)
            ->post(route('admin.instructor-moderation.resumes.approve', $student))
            ->assertNotFound();

        $this->assertSame(ResumeModerationStatus::Draft, ResumeModeration::status($student->fresh()));
    }

    public function test_admin_cannot_view_non_pending_resume_review_page(): void
    {
        $student = User::where('mobile', '09120000003')->firstOrFail();
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.instructor-moderation.resumes.show', $student))
            ->assertNotFound();
    }
}
