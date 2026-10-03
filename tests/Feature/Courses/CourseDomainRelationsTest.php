<?php

namespace Tests\Feature\Courses;

use App\Models\User;
use App\Modules\AdobeConnect\Models\AdobeRecording;
use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\Courses\Enums\ClassInstructorRole;
use App\Modules\Courses\Enums\ClassSessionStatus;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Models\ClassInstructor;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseDomainRelationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_domain_graph_is_fully_linked(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();
        $instructor = User::where('mobile', '09120000002')->firstOrFail();
        $student = User::where('mobile', '09120000003')->firstOrFail();

        $course = Course::query()->create([
            'title' => 'لاراول',
            'slug' => 'laravel-domain-graph',
            'status' => CourseStatus::Published,
            'created_by' => $admin->id,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه A',
            'code' => 'LAR-A-DG',
            'status' => CourseClassStatus::Upcoming,
            'created_by' => $admin->id,
        ]);

        $start = now()->addDays(4)->startOfHour();

        $session = ClassSession::query()->create([
            'course_class_id' => $class->id,
            'session_number' => 1,
            'status' => ClassSessionStatus::Scheduled,
            'scheduled_start_at' => $start,
            'scheduled_end_at' => $start->copy()->addHours(2),
            'created_by' => $admin->id,
        ]);

        ClassInstructor::query()->create([
            'course_class_id' => $class->id,
            'user_id' => $instructor->id,
            'role' => ClassInstructorRole::Lead,
            'is_primary' => true,
        ]);

        $enrollment = Enrollment::query()->create([
            'course_class_id' => $class->id,
            'user_id' => $student->id,
            'status' => EnrollmentStatus::Confirmed,
            'confirmed_at' => now(),
            'price_amount' => 3_000_000,
        ]);

        AdobeRoom::query()->create([
            'course_class_id' => $class->id,
            'class_session_id' => null,
            'sco_id' => 'sco-class-dg',
            'url_path' => '/laravel-dg/',
            'meeting_url' => 'https://connect.example.com/laravel-dg/',
        ]);

        AdobeRoom::query()->create([
            'course_class_id' => $class->id,
            'class_session_id' => $session->id,
            'sco_id' => 'sco-session-dg',
            'url_path' => '/laravel-dg-s1/',
            'meeting_url' => 'https://connect.example.com/laravel-dg-s1/',
        ]);

        AdobeRecording::query()->create([
            'class_session_id' => $session->id,
            'sco_id' => 'rec-dg-1',
            'playback_url' => 'https://connect.example.com/rec/dg-1',
        ]);

        $course->refresh()->load([
            'courseClasses.classSessions',
            'enrollments.user',
            'classSessions',
            'adobeRooms',
        ]);

        $class->refresh()->load([
            'course',
            'classSessions.adobeRecordings',
            'adobeRecordings',
            'classLevelAdobeRooms',
            'sessionAdobeRooms',
            'confirmedEnrollments',
            'students',
        ]);

        $session->refresh()->load(['course', 'enrollments', 'adobeRooms', 'adobeRecordings']);

        $enrollment->refresh()->load('course');
        $instructor->refresh()->load(['taughtCourses', 'teachingCourseClasses']);
        $student->refresh()->load(['enrolledCourses', 'enrolledCourseClasses', 'enrollments.course']);

        $this->assertSame($course->id, $class->course->id);
        $this->assertSame($course->id, $session->course->id);
        $this->assertSame($course->id, $enrollment->course->id);
        $this->assertCount(1, $course->enrollments);
        $this->assertCount(1, $course->classSessions);
        $this->assertCount(2, $course->adobeRooms);
        $this->assertCount(1, $class->confirmedEnrollments);
        $this->assertCount(1, $class->students);
        $this->assertCount(1, $class->classLevelAdobeRooms);
        $this->assertCount(1, $class->sessionAdobeRooms);
        $this->assertCount(1, $class->adobeRecordings);
        $this->assertCount(1, $session->enrollments);
        $this->assertCount(1, $student->enrolledCourses);
        $this->assertSame($course->id, $student->enrolledCourses->first()?->id);
        $this->assertCount(1, $instructor->taughtCourses);
        $this->assertSame($course->id, $instructor->taughtCourses->first()?->id);
        $this->assertSame($course->id, $student->enrollments->first()?->course->id);
    }
}
