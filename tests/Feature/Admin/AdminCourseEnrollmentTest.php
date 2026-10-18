<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Catalog\Models\Category;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Locations\Models\ProvinceCity;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminCourseEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_enroll_eligible_user_via_course_enrollments_page(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();
        $student = User::where('mobile', '09120000003')->firstOrFail();
        $cityId = ProvinceCity::query()->whereNotNull('parent_id')->value('id');
        $student->profile()->updateOrCreate(
            ['user_id' => $student->id],
            ['province_city_id' => $cityId],
        );

        $course = $this->createCourse();
        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'کلاس A',
            'code' => 'A1',
            'status' => CourseClassStatus::Upcoming->value,
            'price' => 1_000_000,
        ]);

        $this->actingAs($admin)
            ->post('/admin/course-enrollments', [
                'course_id' => $course->id,
                'course_class_id' => $class->id,
                'user_id' => $student->id,
                'status' => 'confirmed',
                'price_amount' => 1_000_000,
            ])
            ->assertRedirect(route('admin.course-enrollments.index'));

        $this->assertDatabaseHas('enrollments', [
            'course_class_id' => $class->id,
            'user_id' => $student->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_enrollment_rejected_when_user_profile_incomplete(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $incomplete = User::factory()->create([
            'name' => 'ناقص',
            'family' => null,
            'mobile' => '09127776655',
        ]);

        $course = $this->createCourse();
        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'کلاس B',
            'code' => 'B1',
            'status' => CourseClassStatus::Upcoming->value,
        ]);

        $this->actingAs($admin)
            ->post('/admin/course-enrollments', [
                'course_id' => $course->id,
                'course_class_id' => $class->id,
                'user_id' => $incomplete->id,
                'status' => 'confirmed',
            ])
            ->assertSessionHasErrors('user_id');
    }

    private function createCourse(): Course
    {
        $category = Category::query()->create([
            'title' => 'دسته',
            'slug' => 'cat-enroll',
            'type' => Category::TYPE_COURSE,
            'status' => 1,
        ]);

        $course = Course::query()->create([
            'title' => 'دوره ثبت‌نام',
            'slug' => 'enroll-course-'.Str::lower(Str::random(4)),
            'status' => CourseStatus::Published,
            'published_at' => now(),
        ]);
        $course->categories()->attach($category->id);

        return $course;
    }
}
