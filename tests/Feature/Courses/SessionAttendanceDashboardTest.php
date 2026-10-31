<?php

namespace Tests\Feature\Courses;

use App\Models\User;
use App\Modules\Courses\Enums\ClassInstructorRole;
use App\Modules\Courses\Enums\ClassSessionStatus;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Enums\SessionAttendanceSource;
use App\Modules\Courses\Enums\SessionAttendanceStatus;
use App\Modules\Courses\Models\ClassInstructor;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Courses\Models\SessionAttendance;
use App\Modules\Roles\Support\DefaultRolePermissions;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionAttendanceDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        DefaultRolePermissions::sync();
    }

    public function test_admin_attendance_index_includes_summary_cards(): void
    {
        $admin = User::where('mobile', '09120000001')->firstOrFail();
        $student = User::factory()->create(['mobile' => '09126667788']);

        $course = Course::query()->create([
            'title' => 'Dashboard Course',
            'slug' => 'dashboard-attendance',
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'کلاس',
            'code' => 'DASH-ATT-1',
            'status' => CourseClassStatus::Ongoing,
            'price' => 0,
        ]);

        $session = ClassSession::query()->create([
            'course_class_id' => $class->id,
            'session_number' => 1,
            'title' => 'جلسه داشبورد',
            'status' => ClassSessionStatus::Completed,
            'scheduled_start_at' => now()->subDay(),
            'scheduled_end_at' => now()->subDay()->addHours(2),
        ]);

        Enrollment::query()->create([
            'course_class_id' => $class->id,
            'user_id' => $student->id,
            'status' => EnrollmentStatus::Confirmed,
            'confirmed_at' => now(),
            'price_amount' => 0,
        ]);

        SessionAttendance::query()->create([
            'class_session_id' => $session->id,
            'user_id' => $student->id,
            'status' => SessionAttendanceStatus::Present,
            'duration_minutes' => 40,
            'source' => SessionAttendanceSource::System,
        ]);

        $this->actingAs($admin)
            ->get('/admin/session-attendances')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/SessionAttendances/Index')
                ->where('summary.present_count', 1)
                ->where('summary.attendance_rate', 100)
                ->has('sessions.data', 1)
                ->where('sessions.data.0.present_count', 1));
    }

    public function test_instructor_attendance_index_includes_summary_for_assigned_classes(): void
    {
        $instructor = User::where('mobile', '09120000002')->firstOrFail();
        $student = User::factory()->create(['mobile' => '09125554433']);

        $course = Course::query()->create([
            'title' => 'Instructor Dash Course',
            'slug' => 'instructor-dash-att',
            'status' => CourseStatus::Published,
            'created_by' => $instructor->id,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'کلاس استاد',
            'code' => 'INST-ATT-DASH',
            'status' => CourseClassStatus::Ongoing,
            'price' => 0,
        ]);

        ClassInstructor::query()->create([
            'course_class_id' => $class->id,
            'user_id' => $instructor->id,
            'role' => ClassInstructorRole::Lead,
            'is_primary' => true,
        ]);

        $session = ClassSession::query()->create([
            'course_class_id' => $class->id,
            'session_number' => 1,
            'title' => 'جلسه استاد',
            'status' => ClassSessionStatus::Completed,
            'scheduled_start_at' => now()->subHours(5),
            'scheduled_end_at' => now()->subHours(3),
        ]);

        Enrollment::query()->create([
            'course_class_id' => $class->id,
            'user_id' => $student->id,
            'status' => EnrollmentStatus::Confirmed,
            'confirmed_at' => now(),
            'price_amount' => 0,
        ]);

        SessionAttendance::query()->create([
            'class_session_id' => $session->id,
            'user_id' => $student->id,
            'status' => SessionAttendanceStatus::Absent,
            'source' => SessionAttendanceSource::System,
        ]);

        $this->actingAs($instructor)
            ->get('/instructor/session-attendances')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Instructor/SessionAttendances/Index')
                ->where('summary.absent_count', 1)
                ->has('sessions', 1));
    }
}
