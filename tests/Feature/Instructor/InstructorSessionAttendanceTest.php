<?php

namespace Tests\Feature\Instructor;

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

class InstructorSessionAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        DefaultRolePermissions::sync();
    }

    public function test_assigned_class_instructor_can_view_attendance(): void
    {
        $instructor = User::where('mobile', '09120000002')->firstOrFail();
        $student = User::where('mobile', '09120000003')->firstOrFail();

        $course = Course::query()->create([
            'title' => 'Attend course',
            'slug' => 'attend-course',
            'status' => CourseStatus::Published,
            'created_by' => User::where('mobile', '09120000001')->firstOrFail()->id,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه',
            'code' => 'ATT-01',
            'status' => CourseClassStatus::Upcoming,
        ]);

        $class->instructors()->attach($instructor->id, [
            'role' => 'lead',
            'is_primary' => true,
        ]);

        Enrollment::query()->create([
            'course_class_id' => $class->id,
            'user_id' => $student->id,
            'status' => EnrollmentStatus::Confirmed,
            'confirmed_at' => now(),
            'price_amount' => 0,
        ]);

        $session = ClassSession::query()->create([
            'course_class_id' => $class->id,
            'session_number' => 1,
            'title' => 'جلسه',
            'status' => ClassSessionStatus::Scheduled,
            'scheduled_start_at' => now()->addDay(),
            'scheduled_end_at' => now()->addDay()->addHours(2),
        ]);

        $this->actingAs($instructor)
            ->get('/instructor/session-attendances')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Instructor/SessionAttendances/Index')->has('sessions', 1));

        $this->actingAs($instructor)
            ->get("/instructor/session-attendances/{$session->id}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Instructor/SessionAttendances/Show')
                ->has('rows', 1));
    }

    public function test_course_owner_without_class_assignment_cannot_view_attendance(): void
    {
        $instructor = User::where('mobile', '09120000002')->firstOrFail();

        $course = Course::query()->create([
            'title' => 'Owner only',
            'slug' => 'owner-only-att',
            'status' => CourseStatus::Published,
            'created_by' => $instructor->id,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه',
            'code' => 'OWN-01',
            'status' => CourseClassStatus::Upcoming,
        ]);

        $session = ClassSession::query()->create([
            'course_class_id' => $class->id,
            'session_number' => 1,
            'title' => 'جلسه',
            'status' => ClassSessionStatus::Scheduled,
            'scheduled_start_at' => now()->addDay(),
            'scheduled_end_at' => now()->addDay()->addHours(2),
        ]);

        $this->actingAs($instructor)
            ->get('/instructor/session-attendances')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('sessions', 0));

        $this->actingAs($instructor)
            ->get("/instructor/session-attendances/{$session->id}")
            ->assertForbidden();
    }

    public function test_instructor_cannot_view_attendance_for_other_course(): void
    {
        $instructor = User::where('mobile', '09120000002')->firstOrFail();
        $other = User::where('mobile', '09120000001')->firstOrFail();

        $course = Course::query()->create([
            'title' => 'Other',
            'slug' => 'other-att',
            'status' => CourseStatus::Published,
            'created_by' => $other->id,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه',
            'code' => 'OTH-01',
            'status' => CourseClassStatus::Upcoming,
        ]);

        $session = ClassSession::query()->create([
            'course_class_id' => $class->id,
            'session_number' => 1,
            'title' => 'جلسه',
            'status' => ClassSessionStatus::Scheduled,
            'scheduled_start_at' => now()->addDay(),
            'scheduled_end_at' => now()->addDay()->addHours(2),
        ]);

        $this->actingAs($instructor)
            ->get("/instructor/session-attendances/{$session->id}")
            ->assertForbidden();
    }
}
