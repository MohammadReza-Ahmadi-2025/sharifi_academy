<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCourseClassValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_class_code_is_required_and_must_be_unique(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $course = Course::query()->create([
            'title' => 'دوره اعتبارسنجی کلاس',
            'slug' => 'class-validation-course',
            'status' => CourseStatus::Published,
        ]);

        $this->actingAs($admin)
            ->post('/admin/courses/'.$course->id.'/classes', [
                'name' => 'کلاس بدون کد',
                'code' => '',
                'status' => CourseClassStatus::Upcoming->value,
                'is_public' => true,
            ])
            ->assertSessionHasErrors('code');

        CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'کلاس موجود',
            'code' => 'CLS-DUP',
            'status' => CourseClassStatus::Upcoming,
            'price' => 0,
        ]);

        $this->actingAs($admin)
            ->post('/admin/courses/'.$course->id.'/classes', [
                'name' => 'کلاس تکراری',
                'code' => 'CLS-DUP',
                'status' => CourseClassStatus::Upcoming->value,
                'is_public' => true,
            ])
            ->assertSessionHasErrors('code');
    }
}
