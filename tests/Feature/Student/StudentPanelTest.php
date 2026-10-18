<?php

namespace Tests\Feature\Student;

use App\Models\User;
use App\Modules\Courses\Enums\ClassSessionStatus;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Roles\Support\DefaultRolePermissions;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        DefaultRolePermissions::sync();
    }

    public function test_guest_cannot_open_student_dashboard(): void
    {
        $this->get('/student/dashboard')->assertRedirect();
    }

    public function test_instructor_cannot_open_student_dashboard(): void
    {
        $instructor = User::where('mobile', '09120000002')->firstOrFail();

        $this->actingAs($instructor)
            ->get('/student/dashboard')
            ->assertForbidden();
    }

    public function test_student_sees_confirmed_enrollments_and_sessions(): void
    {
        $student = User::where('mobile', '09120000003')->firstOrFail();

        $course = Course::query()->create([
            'title' => 'Laravel پیشرفته',
            'slug' => 'laravel-advanced-student',
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه A',
            'code' => 'LRV-A-01',
            'status' => CourseClassStatus::Upcoming,
            'price' => 1_000_000,
        ]);

        Enrollment::query()->create([
            'course_class_id' => $class->id,
            'user_id' => $student->id,
            'status' => EnrollmentStatus::Confirmed,
            'confirmed_at' => now(),
            'price_amount' => 1_000_000,
        ]);

        $otherClass = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه B',
            'code' => 'LRV-B-01',
            'status' => CourseClassStatus::Upcoming,
            'price' => 1_000_000,
        ]);

        Enrollment::query()->create([
            'course_class_id' => $otherClass->id,
            'user_id' => $student->id,
            'status' => EnrollmentStatus::Pending,
            'price_amount' => 1_000_000,
        ]);

        ClassSession::query()->create([
            'course_class_id' => $class->id,
            'session_number' => 1,
            'title' => 'جلسه اول',
            'status' => ClassSessionStatus::Scheduled,
            'scheduled_start_at' => now()->addDay(),
            'scheduled_end_at' => now()->addDay()->addHours(2),
        ]);

        $this->actingAs($student)
            ->get('/student/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Student/Dashboard')
                ->has('enrollments', 1)
                ->where('enrollments.0.course.title', 'Laravel پیشرفته')
                ->has('enrollments.0.sessions', 1)
                ->where('enrollments.0.sessions.0.title', 'جلسه اول'));
    }
}
