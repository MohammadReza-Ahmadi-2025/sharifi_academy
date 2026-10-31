<?php

namespace Tests\Feature\Instructor;

use App\Models\User;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Roles\Support\DefaultRolePermissions;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorCourseShowRosterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        DefaultRolePermissions::sync();
    }

    public function test_instructor_can_open_course_show_and_roster(): void
    {
        $instructor = User::where('mobile', '09120000002')->firstOrFail();
        $student = User::where('mobile', '09120000003')->firstOrFail();

        $course = Course::query()->create([
            'title' => 'دوره نمای استاد',
            'slug' => 'instructor-show-course',
            'status' => CourseStatus::Published,
            'created_by' => $instructor->id,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه ۱',
            'code' => 'SHOW-1',
            'status' => CourseClassStatus::Upcoming,
            'price' => 0,
        ]);

        Enrollment::query()->create([
            'course_class_id' => $class->id,
            'user_id' => $student->id,
            'status' => EnrollmentStatus::Confirmed,
            'confirmed_at' => now(),
            'price_amount' => 0,
        ]);

        $this->actingAs($instructor)
            ->get("/instructor/courses/{$course->id}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Instructor/Courses/Show')
                ->where('course.title', 'دوره نمای استاد')
                ->has('classes', 1));

        $this->actingAs($instructor)
            ->get("/instructor/courses/{$course->id}/roster")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Instructor/Courses/Roster')
                ->has('enrollments', 1));
    }
}
