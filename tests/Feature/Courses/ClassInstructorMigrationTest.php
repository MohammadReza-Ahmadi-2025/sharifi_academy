<?php

namespace Tests\Feature\Courses;

use App\Modules\Courses\Enums\ClassInstructorRole;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\ClassInstructor;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassInstructorMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_class_can_have_instructors_with_roles(): void
    {
        $this->seed(DatabaseSeeder::class);

        $instructor = User::where('mobile', '09120000002')->firstOrFail();
        $assistant = User::where('mobile', '09120000001')->firstOrFail();

        $course = Course::query()->create([
            'title' => 'لاراول',
            'slug' => 'laravel-instructors',
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه A',
            'code' => 'LAR-A-01',
            'status' => CourseClassStatus::Upcoming,
        ]);

        ClassInstructor::query()->create([
            'course_class_id' => $class->id,
            'user_id' => $instructor->id,
            'role' => ClassInstructorRole::Lead,
            'is_primary' => true,
            'share_percentage' => 60,
        ]);

        ClassInstructor::query()->create([
            'course_class_id' => $class->id,
            'user_id' => $assistant->id,
            'role' => ClassInstructorRole::Assistant,
            'is_primary' => false,
        ]);

        $class->refresh()->load(['instructors', 'primaryInstructorAssignment.user']);

        $this->assertCount(2, $class->instructors);
        $this->assertSame($instructor->id, $class->primaryInstructorAssignment?->user_id);
        $this->assertCount(1, $instructor->teachingCourseClasses);
        $this->assertSame('استاد اصلی', $class->primaryInstructorAssignment?->role_label);
    }
}
