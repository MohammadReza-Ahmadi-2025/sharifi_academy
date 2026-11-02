<?php

namespace Tests\Feature\Student;

use App\Models\User;
use App\Modules\Profile\Enums\ResumeModerationStatus;
use App\Modules\Profile\Support\ResumeModeration;
use App\Modules\Roles\Support\DefaultRolePermissions;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentResumeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        DefaultRolePermissions::sync();
    }

    public function test_student_can_manage_and_submit_resume_for_admin_approval(): void
    {
        $student = User::where('mobile', '09120000003')->firstOrFail();
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($student)
            ->get('/student/resume')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Student/Resume/Index')
                ->where('panel.panel', 'student')
                ->where('panel.base_path', '/student/resume'));

        $this->actingAs($student)
            ->post('/student/resume', [
                'type' => 'education',
                'title' => 'کارشناسی نرم‌افزار',
                'organization' => 'دانشگاه آزاد',
                'is_current' => false,
            ])
            ->assertRedirect(route('student.resume.index'));

        $this->assertDatabaseHas('resumes', [
            'user_id' => $student->id,
            'title' => 'کارشناسی نرم‌افزار',
        ]);

        $this->actingAs($student)
            ->post(route('student.resume.submit'))
            ->assertRedirect(route('student.resume.index'));

        $this->assertSame(ResumeModerationStatus::Pending, ResumeModeration::status($student->fresh()));

        $this->actingAs($admin)
            ->get(route('admin.instructor-moderation.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('pendingResumes', 1)
                ->where('pendingResumes.0.id', $student->id)
                ->where('pendingResumes.0.role_label', 'دانشجو'));

        $this->actingAs($admin)
            ->post(route('admin.instructor-moderation.resumes.approve', $student))
            ->assertRedirect();

        $this->assertSame(ResumeModerationStatus::Approved, ResumeModeration::status($student->fresh()));

        // Student resumes stay off the public instructor pages.
        $this->get(route('instructors.show', $student))->assertNotFound();
    }

    public function test_guest_is_redirected_from_student_resume(): void
    {
        $this->get('/student/resume')->assertRedirect('/login');
    }
}
