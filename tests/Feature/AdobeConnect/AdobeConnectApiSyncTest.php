<?php

namespace Tests\Feature\AdobeConnect;

use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\AdobeConnect\Services\AdobeRoomPayloadFactory;
use App\Modules\AdobeConnect\Services\SyncAdobeSessionRecordingsService;
use App\Modules\AdobeConnect\Support\AdobeConnectConfig;
use App\Modules\Courses\Enums\ClassSessionStatus;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdobeConnectApiSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('adobe_connect.base_url', 'https://connect.test');
        Config::set('adobe_connect.login', 'api@example.com');
        Config::set('adobe_connect.api_token', 'secret-token');
    }

    public function test_room_payload_factory_fetches_sco_info_when_url_path_missing(): void
    {
        Http::fake(function ($request) {
            $url = $request->url();
            if (str_contains($url, 'action=login')) {
                return Http::response(
                    '<?xml version="1.0"?><results><status code="ok"/></results>',
                    200,
                    ['Set-Cookie' => 'BREEZESESSION=fake-session; Path=/'],
                );
            }

            if (str_contains($url, 'action=sco-info') && str_contains($url, 'sco-id=9001')) {
                return Http::response(<<<'XML'
<?xml version="1.0"?>
<results>
  <status code="ok"/>
  <sco sco-id="9001" type="meeting" icon="meeting">
    <name>Live Class</name>
    <url-path>/live-room/</url-path>
  </sco>
</results>
XML);
            }

            return Http::response('<?xml version="1.0"?><results><status code="invalid"/></results>', 200);
        });

        $factory = app(AdobeRoomPayloadFactory::class);
        $payload = $factory->resolveForCreate([
            'sco_id' => '9001',
            'access_type' => 'protected',
            'status' => 'active',
        ]);

        $this->assertSame('/live-room/', $payload['url_path']);
        $this->assertSame('https://connect.test/live-room/', $payload['meeting_url']);
        $this->assertTrue(app(AdobeConnectConfig::class)->isApiConfigured());
    }

    public function test_sync_session_recordings_imports_archives(): void
    {
        Http::fake(function ($request) {
            $url = $request->url();
            if (str_contains($url, 'action=login')) {
                return Http::response(
                    '<?xml version="1.0"?><results><status code="ok"/></results>',
                    200,
                    ['Set-Cookie' => 'BREEZESESSION=fake-session; Path=/'],
                );
            }

            if (str_contains($url, 'action=sco-contents')) {
                return Http::response(<<<'XML'
<?xml version="1.0"?>
<results>
  <status code="ok"/>
  <scos>
    <sco sco-id="9100" icon="archive">
      <name>Recording 1</name>
      <url-path>/archive/rec1/</url-path>
      <duration>00:10:00.000</duration>
    </sco>
  </scos>
</results>
XML);
            }

            if (str_contains($url, 'action=sco-info') && str_contains($url, 'sco-id=9100')) {
                return Http::response(<<<'XML'
<?xml version="1.0"?>
<results>
  <status code="ok"/>
  <sco sco-id="9100"><enhanced-av>true</enhanced-av></sco>
</results>
XML);
            }

            return Http::response('<?xml version="1.0"?><results><status code="ok"/></results>', 200);
        });

        $course = Course::query()->create(['title' => 'C', 'slug' => 'c']);
        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'G',
            'code' => 'G1',
            'status' => CourseClassStatus::Upcoming,
        ]);
        $session = ClassSession::query()->create([
            'course_class_id' => $class->id,
            'session_number' => 1,
            'status' => ClassSessionStatus::Scheduled,
            'scheduled_start_at' => now()->addDay(),
            'scheduled_end_at' => now()->addDay()->addHour(),
        ]);

        AdobeRoom::query()->create([
            'course_class_id' => $class->id,
            'class_session_id' => $session->id,
            'sco_id' => '9001',
            'url_path' => '/live-room/',
            'meeting_url' => 'https://connect.test/live-room/',
            'access_type' => 'protected',
            'status' => 'active',
        ]);

        $sync = app(SyncAdobeSessionRecordingsService::class);
        $recordings = $sync->syncForSession($session, $class);

        $this->assertCount(1, $recordings);
        $this->assertDatabaseHas('adobe_recordings', [
            'sco_id' => '9100',
            'class_session_id' => $session->id,
            'duration_seconds' => 600,
        ]);
        $this->assertSame('https://connect.test/archive/rec1/', $recordings[0]->playback_url);
        $this->assertStringContainsString('download=true', (string) $recordings[0]->download_url);
    }
}
