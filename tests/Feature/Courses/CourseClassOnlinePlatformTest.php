<?php

namespace Tests\Feature\Courses;

use App\Models\User;
use App\Modules\Courses\Enums\ClassSessionStatus;
use App\Modules\Courses\Enums\CourseClassOnlinePlatform;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseClassOnlinePlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_video_link_class_and_session_requires_video_url(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $course = Course::query()->create([
            'title' => 'دوره ویدیو',
            'slug' => 'video-course',
            'status' => CourseStatus::Published,
        ]);

        $this->actingAs($admin)
            ->post('/admin/courses/'.$course->id.'/classes', [
                'name' => 'کلاس ویدیو',
                'code' => 'VID-1',
                'status' => CourseClassStatus::Upcoming->value,
                'delivery_type' => 'online',
                'online_platform' => CourseClassOnlinePlatform::VideoLink->value,
                'is_public' => true,
            ])
            ->assertRedirect();

        $class = CourseClass::query()->where('code', 'VID-1')->firstOrFail();
        $this->assertTrue($class->usesVideoLink());

        $this->actingAs($admin)
            ->post("/admin/courses/{$course->id}/classes/{$class->id}/sessions", [
                'session_number' => 1,
                'title' => 'جلسه ۱',
                'status' => ClassSessionStatus::Scheduled->value,
                'scheduled_start_at' => now()->addDay()->toDateTimeString(),
                'scheduled_end_at' => now()->addDay()->addHours(2)->toDateTimeString(),
            ])
            ->assertSessionHasErrors('video_url');

        $this->actingAs($admin)
            ->post("/admin/courses/{$course->id}/classes/{$class->id}/sessions", [
                'session_number' => 1,
                'title' => 'جلسه ۱',
                'status' => ClassSessionStatus::Scheduled->value,
                'scheduled_start_at' => now()->addDay()->toDateTimeString(),
                'scheduled_end_at' => now()->addDay()->addHours(2)->toDateTimeString(),
                'video_url' => 'https://cdn.example.com/lesson-1.mp4',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('class_sessions', [
            'course_class_id' => $class->id,
            'video_url' => 'https://cdn.example.com/lesson-1.mp4',
        ]);
    }

    public function test_public_course_page_shows_online_platform_label(): void
    {
        $course = Course::query()->create([
            'title' => 'دوره عمومی',
            'slug' => 'public-video-course',
            'status' => CourseStatus::Published,
            'published_at' => now(),
        ]);

        CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه A',
            'code' => 'A1',
            'status' => CourseClassStatus::Upcoming,
            'delivery_type' => 'online',
            'online_platform' => CourseClassOnlinePlatform::VideoLink,
            'is_public' => true,
            'price' => 0,
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addMonths(2),
        ]);

        $this->get('/courses/'.$course->publicSlug())
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('course.classes.0.online_platform_label', 'لینک ویدیو (آنلاین)'));
    }
}
