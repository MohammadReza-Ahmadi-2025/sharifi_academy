<?php

namespace Tests\Feature\Courses;

use App\Modules\Courses\Enums\CourseClassDeliveryType;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseClassMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_class_belongs_to_course_and_creator(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $course = Course::query()->create([
            'title' => 'لاراول',
            'slug' => 'laravel',
            'status' => CourseStatus::Published,
            'created_by' => $admin->id,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه پاییز ۱۴۰۵',
            'code' => 'LAR-101-FALL-1405-01',
            'status' => CourseClassStatus::Upcoming,
            'delivery_type' => CourseClassDeliveryType::Hybrid,
            'price' => 2_500_000,
            'is_public' => true,
            'created_by' => $admin->id,
        ]);

        $course->refresh();
        $admin->refresh();

        $this->assertSame($course->id, $class->course?->id);
        $this->assertCount(1, $course->courseClasses);
        $this->assertSame(CourseClassStatus::Upcoming, $class->status);
        $this->assertSame('ترکیبی', $class->delivery_type_label);
        $this->assertCount(1, $admin->createdCourseClasses);
    }
}
