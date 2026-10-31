<?php

namespace Tests\Feature\AdobeConnect;

use App\Models\User;
use App\Modules\AdobeConnect\Enums\AdobeRoomAccessType;
use App\Modules\AdobeConnect\Enums\AdobeRoomStatus;
use App\Modules\AdobeConnect\Models\AdobeRecording;
use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\AdobeConnect\Services\AutoSyncAdobeConnectSessionsService;
use App\Modules\Courses\Enums\ClassSessionStatus;
use App\Modules\Courses\Enums\CourseClassDeliveryType;
use App\Modules\Courses\Enums\CourseClassOnlinePlatform;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Enums\SessionAttendanceStatus;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Courses\Models\SessionAttendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdobeConnectAutoSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('adobe_connect.base_url', 'https://connect.test');
        Config::set('adobe_connect.login', 'api@example.com');
        Config::set('adobe_connect.api_token', 'secret-token');
        Config::set('adobe_connect.auto_sync_enabled', true);
    }

    public function test_auto_sync_imports_recent_recordings_and_attendance(): void
    {
        $student = User::factory()->create([
            'mobile' => '09124445566',
            'adobe_connect_principal_id' => '333',
        ]);

        $course = Course::query()->create([
            'title' => 'Auto Sync',
            'slug' => 'auto-sync-course',
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'کلاس',
            'code' => 'AUTO-SYNC-1',
            'status' => CourseClassStatus::Ongoing,
            'delivery_type' => CourseClassDeliveryType::Online,
            'online_platform' => CourseClassOnlinePlatform::AdobeConnect,
            'price' => 0,
        ]);

        $session = ClassSession::query()->create([
            'course_class_id' => $class->id,
            'session_number' => 1,
            'title' => 'جلسه اخیر',
            'status' => ClassSessionStatus::Completed,
            'scheduled_start_at' => now()->subHours(3),
            'scheduled_end_at' => now()->subHour(),
        ]);

        AdobeRoom::query()->create([
            'course_class_id' => $class->id,
            'sco_id' => 'sco-auto-1',
            'url_path' => '/auto-room/',
            'meeting_url' => 'https://connect.test/auto-room/',
            'access_type' => AdobeRoomAccessType::Protected,
            'status' => AdobeRoomStatus::Active,
        ]);

        Enrollment::query()->create([
            'course_class_id' => $class->id,
            'user_id' => $student->id,
            'status' => EnrollmentStatus::Confirmed,
            'confirmed_at' => now(),
            'price_amount' => 0,
        ]);

        Http::fake(function ($request) use ($session) {
            $url = $request->url();
            if (str_contains($url, 'action=login')) {
                return Http::response(
                    '<?xml version="1.0"?><results><status code="ok"/></results>',
                    200,
                    ['Set-Cookie' => 'BREEZESESSION=fake-session; Path=/'],
                );
            }

            if (str_contains($url, 'action=sco-contents')) {
                $begin = $session->scheduled_start_at->copy()->addMinutes(5)->toIso8601String();

                return Http::response(<<<XML
<?xml version="1.0"?><results><status code="ok"/>
  <scos>
    <sco sco-id="rec-1" icon="archive">
      <name>Recording 1</name>
      <url-path>/auto-room/recording/</url-path>
      <duration>01:10:00</duration>
      <date-begin>{$begin}</date-begin>
    </sco>
  </scos>
</results>
XML);
            }

            if (str_contains($url, 'action=sco-info')) {
                return Http::response('<?xml version="1.0"?><results><status code="ok"/><sco sco-id="rec-1"/></results>');
            }

            if (str_contains($url, 'action=report-meeting-attendance')) {
                return Http::response(<<<'XML'
<?xml version="1.0"?><results><status code="ok"/>
  <report-meeting-attendance>
    <row transcript-id="9" sco-id="sco-auto-1" principal-id="333">
      <login>09124445566</login>
      <date-created>2026-10-30T10:00:00.000+03:30</date-created>
      <date-end>2026-10-30T11:00:00.000+03:30</date-end>
    </row>
  </report-meeting-attendance>
</results>
XML);
            }

            return Http::response('<?xml version="1.0"?><results><status code="invalid"/></results>', 200);
        });

        $stats = app(AutoSyncAdobeConnectSessionsService::class)->syncRecent(48);

        $this->assertSame(1, $stats['sessions']);
        $this->assertGreaterThanOrEqual(1, $stats['recordings_synced']);
        $this->assertSame(1, $stats['attendance_synced']);
        $this->assertDatabaseHas('adobe_recordings', [
            'class_session_id' => $session->id,
            'sco_id' => 'rec-1',
        ]);
        $this->assertDatabaseHas('session_attendances', [
            'class_session_id' => $session->id,
            'user_id' => $student->id,
            'status' => SessionAttendanceStatus::Present->value,
        ]);
    }

    public function test_auto_sync_command_is_registered(): void
    {
        $this->artisan('adobe-connect:auto-sync', ['--hours' => 24, '--no-attendance' => true, '--no-recordings' => true])
            ->assertSuccessful();
    }
}
