<?php

namespace Tests\Feature\AdobeConnect;

use App\Models\User;
use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\Courses\Enums\ClassSessionStatus;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdobeConnectAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_role_receives_adobe_management_permissions(): void
    {
        $this->seed(DatabaseSeeder::class);

        $instructor = User::where('mobile', '09120000002')->firstOrFail();

        $this->assertTrue($instructor->can('adobe-rooms-index'));
        $this->assertTrue($instructor->can('adobe-rooms-create'));
        $this->assertTrue($instructor->can('adobe-rooms-update'));
        $this->assertTrue($instructor->can('adobe-rooms-delete'));
        $this->assertTrue($instructor->can('adobe-recordings-index'));
        $this->assertTrue($instructor->can('adobe-recordings-create'));
        $this->assertTrue($instructor->can('adobe-recordings-delete'));
    }

    public function test_instructor_can_manage_adobe_room_only_for_owned_course(): void
    {
        $this->seed(DatabaseSeeder::class);

        $owner = User::where('mobile', '09120000002')->firstOrFail();
        $otherInstructor = User::factory()->create();
        $otherInstructor->assignRole('instructor');

        [$course, $class] = $this->ownedCourseAndClass($owner);

        $payload = [
            'sco_id' => 'meeting-100',
            'url_path' => '/meeting-100/',
            'meeting_url' => 'https://connect.example.com/meeting-100/',
            'access_type' => 'protected',
            'status' => 'active',
        ];

        $this->actingAs($otherInstructor)
            ->post("/instructor/courses/{$course->id}/classes/{$class->id}/adobe-rooms", $payload)
            ->assertForbidden();

        $this->actingAs($owner)
            ->post("/instructor/courses/{$course->id}/classes/{$class->id}/adobe-rooms", $payload)
            ->assertRedirect(route('instructor.courses.classes.edit', [$course, $class]));

        $this->assertDatabaseHas('adobe_rooms', [
            'course_class_id' => $class->id,
            'sco_id' => 'meeting-100',
        ]);
    }

    public function test_student_cannot_sync_or_delete_adobe_resources(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();
        $student = User::where('mobile', '09120000003')->firstOrFail();
        [$course, $class, $session] = $this->ownedCourseClassAndSession($admin);

        $room = AdobeRoom::query()->create([
            'course_class_id' => $class->id,
            'class_session_id' => $session->id,
            'sco_id' => 'meeting-200',
            'url_path' => '/meeting-200/',
            'meeting_url' => 'https://connect.example.com/meeting-200/',
            'access_type' => 'protected',
            'status' => 'active',
        ]);

        $this->actingAs($student)
            ->post("/admin/courses/{$course->id}/classes/{$class->id}/sessions/{$session->id}/adobe-rooms/{$room->id}/sync")
            ->assertForbidden();

        $this->actingAs($student)
            ->delete("/admin/courses/{$course->id}/classes/{$class->id}/sessions/{$session->id}/adobe-rooms/{$room->id}")
            ->assertForbidden();
    }

    /**
     * @return array{Course, CourseClass}
     */
    private function ownedCourseAndClass(User $owner): array
    {
        $course = Course::query()->create([
            'title' => 'Adobe Course',
            'slug' => 'adobe-course-'.$owner->id,
            'created_by' => $owner->id,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'Adobe Class',
            'code' => 'AC-'.$owner->id,
            'status' => CourseClassStatus::Upcoming,
        ]);

        return [$course, $class];
    }

    /**
     * @return array{Course, CourseClass, ClassSession}
     */
    private function ownedCourseClassAndSession(User $owner): array
    {
        [$course, $class] = $this->ownedCourseAndClass($owner);

        $session = ClassSession::query()->create([
            'course_class_id' => $class->id,
            'session_number' => 1,
            'status' => ClassSessionStatus::Scheduled,
            'scheduled_start_at' => now()->addDay(),
            'scheduled_end_at' => now()->addDay()->addHour(),
        ]);

        return [$course, $class, $session];
    }
}
