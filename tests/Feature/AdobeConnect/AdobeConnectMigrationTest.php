<?php

namespace Tests\Feature\AdobeConnect;

use App\Modules\AdobeConnect\Enums\AdobeRoomAccessType;
use App\Modules\AdobeConnect\Enums\AdobeRoomStatus;
use App\Modules\AdobeConnect\Models\AdobeRecording;
use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\Courses\Enums\ClassSessionStatus;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdobeConnectMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_class_can_have_per_class_and_per_session_adobe_rooms(): void
    {
        $course = Course::query()->create([
            'title' => 'لاراول',
            'slug' => 'laravel-adobe',
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه A',
            'code' => 'LAR-ADOBE',
            'status' => CourseClassStatus::Upcoming,
        ]);

        $start = now()->addDays(2)->startOfHour();

        $session = ClassSession::query()->create([
            'course_class_id' => $class->id,
            'session_number' => 1,
            'status' => ClassSessionStatus::Scheduled,
            'scheduled_start_at' => $start,
            'scheduled_end_at' => $start->copy()->addHours(2),
        ]);

        AdobeRoom::query()->create([
            'course_class_id' => $class->id,
            'class_session_id' => null,
            'sco_id' => 'sco-class-1001',
            'url_path' => '/laravel-1405/',
            'meeting_url' => 'https://connect.example.com/laravel-1405/',
            'access_type' => AdobeRoomAccessType::Protected,
            'status' => AdobeRoomStatus::Active,
        ]);

        AdobeRoom::query()->create([
            'course_class_id' => $class->id,
            'class_session_id' => $session->id,
            'sco_id' => 'sco-session-1001',
            'url_path' => '/laravel-1405-session-1/',
            'meeting_url' => 'https://connect.example.com/laravel-1405-session-1/',
            'access_type' => AdobeRoomAccessType::Public,
            'status' => AdobeRoomStatus::Active,
        ]);

        $class->refresh()->load('adobeRooms.classSession');
        $session->refresh();

        $this->assertCount(2, $class->adobeRooms);
        $this->assertTrue($class->adobeRooms->firstWhere('class_session_id', null)?->isPerSession() === false);
        $this->assertTrue($class->adobeRooms->firstWhere('class_session_id', $session->id)?->isPerSession());
        $this->assertCount(1, $session->adobeRooms);
    }

    public function test_session_can_have_public_recordings(): void
    {
        $course = Course::query()->create([
            'title' => 'React',
            'slug' => 'react-adobe-rec',
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه B',
            'code' => 'RCT-REC',
            'status' => CourseClassStatus::Ongoing,
        ]);

        $start = now()->subDay()->startOfHour();

        $session = ClassSession::query()->create([
            'course_class_id' => $class->id,
            'session_number' => 1,
            'status' => ClassSessionStatus::Completed,
            'scheduled_start_at' => $start,
            'scheduled_end_at' => $start->copy()->addHours(2),
            'actual_end_at' => $start->copy()->addHours(2),
        ]);

        AdobeRecording::query()->create([
            'class_session_id' => $session->id,
            'sco_id' => 'rec-sco-9001',
            'name' => 'ضبط جلسه اول',
            'playback_url' => 'https://connect.example.com/rec/9001',
            'duration_seconds' => 5400,
            'size_bytes' => 1_500_000_000,
            'is_public' => true,
            'is_passcode_protected' => false,
        ]);

        $public = $session->fresh()->adobeRecordings()->public()->get();

        $this->assertCount(1, $public);
        $this->assertSame('ضبط جلسه اول', $public->first()?->name);
        $this->assertSame(5400, $public->first()?->duration_seconds);
    }
}
