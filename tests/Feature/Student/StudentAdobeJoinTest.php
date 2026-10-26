<?php

namespace Tests\Feature\Student;

use App\Models\User;
use App\Modules\AdobeConnect\Enums\AdobeRoomAccessType;
use App\Modules\AdobeConnect\Enums\AdobeRoomStatus;
use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\Courses\Enums\ClassSessionStatus;
use App\Modules\Courses\Enums\CourseClassOnlinePlatform;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Roles\Support\DefaultRolePermissions;
use App\Modules\Settings\Models\SiteSetting;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAdobeJoinTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        DefaultRolePermissions::sync();
    }

    public function test_student_redirects_to_adobe_when_enrolled_and_room_configured(): void
    {
        $student = User::where('mobile', '09120000003')->firstOrFail();

        SiteSetting::query()->updateOrCreate(
            ['key' => 'adobe_connect_base_url'],
            [
                'value' => 'https://meet.example.com',
                'type' => 'text',
                'group' => 'integrations',
                'autoload' => false,
            ],
        );

        $course = Course::query()->create([
            'title' => 'Adobe Test',
            'slug' => 'adobe-test-course',
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه',
            'code' => 'ADOBE-01',
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

        $session = ClassSession::query()->create([
            'course_class_id' => $class->id,
            'session_number' => 1,
            'title' => 'جلسه',
            'status' => ClassSessionStatus::Scheduled,
            'scheduled_start_at' => now()->addDay(),
            'scheduled_end_at' => now()->addDay()->addHours(2),
        ]);

        AdobeRoom::query()->create([
            'course_class_id' => $class->id,
            'class_session_id' => $session->id,
            'sco_id' => 'sco-1',
            'url_path' => '/laravel-live/',
            'meeting_url' => '',
            'access_type' => AdobeRoomAccessType::Public,
            'status' => AdobeRoomStatus::Active,
        ]);

        $this->actingAs($student)
            ->get("/student/classes/{$class->id}/sessions/{$session->id}/adobe")
            ->assertRedirect('https://meet.example.com/laravel-live/');
    }

    public function test_student_cannot_join_adobe_without_enrollment(): void
    {
        $student = User::where('mobile', '09120000003')->firstOrFail();

        $course = Course::query()->create([
            'title' => 'No enroll',
            'slug' => 'no-enroll-adobe',
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه',
            'code' => 'NO-EN-01',
            'status' => CourseClassStatus::Upcoming,
            'price' => 0,
        ]);

        $session = ClassSession::query()->create([
            'course_class_id' => $class->id,
            'session_number' => 1,
            'title' => 'جلسه',
            'status' => ClassSessionStatus::Scheduled,
            'scheduled_start_at' => now()->addDay(),
            'scheduled_end_at' => now()->addDay()->addHours(2),
        ]);

        $this->actingAs($student)
            ->get("/student/classes/{$class->id}/sessions/{$session->id}/adobe")
            ->assertForbidden();
    }

    public function test_dashboard_includes_adobe_join_route_when_room_exists(): void
    {
        $student = User::where('mobile', '09120000003')->firstOrFail();

        SiteSetting::query()->updateOrCreate(
            ['key' => 'adobe_connect_base_url'],
            [
                'value' => 'https://meet.example.com',
                'type' => 'text',
                'group' => 'integrations',
                'autoload' => false,
            ],
        );

        $course = Course::query()->create([
            'title' => 'Dash Adobe',
            'slug' => 'dash-adobe',
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه',
            'code' => 'DASH-01',
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

        $session = ClassSession::query()->create([
            'course_class_id' => $class->id,
            'session_number' => 1,
            'title' => 'جلسه',
            'status' => ClassSessionStatus::Scheduled,
            'scheduled_start_at' => now()->addDay(),
            'scheduled_end_at' => now()->addDay()->addHours(2),
        ]);

        AdobeRoom::query()->create([
            'course_class_id' => $class->id,
            'class_session_id' => null,
            'sco_id' => 'sco-class',
            'url_path' => '/class-room/',
            'meeting_url' => '',
            'access_type' => AdobeRoomAccessType::Public,
            'status' => AdobeRoomStatus::Active,
        ]);

        $expectedUrl = route('student.sessions.join', [
            'courseClass' => $class->id,
            'classSession' => $session->id,
        ]);

        $this->actingAs($student)
            ->get('/student/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('enrollments.0.sessions.0.adobe_connect_url', $expectedUrl));
    }

    public function test_student_redirects_to_video_url_when_class_uses_video_platform(): void
    {
        $student = User::where('mobile', '09120000003')->firstOrFail();

        $course = Course::query()->create([
            'title' => 'Video Test',
            'slug' => 'video-test-course',
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه ویدیو',
            'code' => 'VIDEO-01',
            'status' => CourseClassStatus::Upcoming,
            'online_platform' => CourseClassOnlinePlatform::VideoLink,
            'price' => 0,
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
            'title' => 'جلسه ویدیو',
            'status' => ClassSessionStatus::Scheduled,
            'scheduled_start_at' => now()->addDay(),
            'scheduled_end_at' => now()->addDay()->addHours(2),
            'video_url' => 'https://video.example.com/live/lesson-1',
        ]);

        $this->actingAs($student)
            ->get("/student/classes/{$class->id}/sessions/{$session->id}/adobe")
            ->assertRedirect('https://video.example.com/live/lesson-1');

        $this->actingAs($student)
            ->get('/student/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('enrollments.0.sessions.0.join_label', 'تماشای ویدیو')
                ->has('enrollments.0.sessions.0.join_url'));
    }
}
