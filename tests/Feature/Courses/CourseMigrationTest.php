<?php

namespace Tests\Feature\Courses;

use App\Modules\Courses\Enums\CourseLevel;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\Course;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_belongs_to_creator_user(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $course = Course::query()->create([
            'title' => 'لاراول از صفر',
            'slug' => 'laravel-from-zero',
            'code' => 'LAR-101',
            'level' => CourseLevel::Beginner,
            'status' => CourseStatus::Draft,
            'created_by' => $admin->id,
        ]);

        $admin->refresh();

        $this->assertSame($admin->id, $course->creator?->id);
        $this->assertCount(1, $admin->createdCourses);
        $this->assertSame('LAR-101', $admin->createdCourses->first()?->code);
    }
}
