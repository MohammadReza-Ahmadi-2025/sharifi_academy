<?php

namespace Tests\Feature\Courses;

use App\Models\User;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_enroll_in_course_class_with_locked_price(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = User::where('mobile', '09120000003')->firstOrFail();

        $course = Course::query()->create([
            'title' => 'React',
            'slug' => 'react-enrollment',
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه B',
            'code' => 'RCT-B-01',
            'status' => CourseClassStatus::Upcoming,
            'price' => 5_000_000,
        ]);

        $enrollment = Enrollment::query()->create([
            'course_class_id' => $class->id,
            'user_id' => $student->id,
            'status' => EnrollmentStatus::Pending,
            'reserved_until' => now()->addHour(),
            'price_amount' => 4_500_000,
        ]);

        $class->update(['price' => 6_000_000]);

        $enrollment->refresh();
        $class->refresh()->load('enrollments.user');

        $this->assertSame(EnrollmentStatus::Pending, $enrollment->status);
        $this->assertSame(4_500_000, $enrollment->price_amount);
        $this->assertTrue($enrollment->isPending());
        $this->assertCount(1, $class->enrollments);
        $this->assertCount(1, $student->enrollments);
        $this->assertSame($class->id, $student->enrolledCourseClasses->first()?->id);
    }

    public function test_duplicate_enrollment_per_class_is_rejected(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = User::where('mobile', '09120000003')->firstOrFail();

        $course = Course::query()->create([
            'title' => 'Vue',
            'slug' => 'vue-enrollment-unique',
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه C',
            'code' => 'VUE-C-01',
            'status' => CourseClassStatus::Upcoming,
        ]);

        Enrollment::query()->create([
            'course_class_id' => $class->id,
            'user_id' => $student->id,
            'status' => EnrollmentStatus::Confirmed,
            'confirmed_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        Enrollment::query()->create([
            'course_class_id' => $class->id,
            'user_id' => $student->id,
            'status' => EnrollmentStatus::Pending,
        ]);
    }
}
