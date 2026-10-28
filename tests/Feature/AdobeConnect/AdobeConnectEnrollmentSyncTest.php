<?php

namespace Tests\Feature\AdobeConnect;

use App\Models\User;
use App\Modules\AdobeConnect\Enums\AdobeRoomAccessType;
use App\Modules\AdobeConnect\Enums\AdobeRoomStatus;
use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\AdobeConnect\Services\SyncAdobeConnectEnrollmentService;
use App\Modules\Courses\Enums\CourseClassDeliveryType;
use App\Modules\Courses\Enums\CourseClassOnlinePlatform;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdobeConnectEnrollmentSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('adobe_connect.base_url', 'https://connect.test');
        Config::set('adobe_connect.login', 'api@example.com');
        Config::set('adobe_connect.api_token', 'secret-token');
    }

    public function test_confirmed_enrollment_sync_registers_user_and_grants_meeting_access(): void
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

            if (str_contains($url, 'action=principal-list')) {
                return Http::response('<?xml version="1.0"?><results><status code="ok"/><principal-list/></results>');
            }

            if (str_contains($url, 'action=principal-update') && str_contains($url, 'folder-id=')) {
                return Http::response(<<<'XML'
<?xml version="1.0"?><results><status code="ok"/>
  <principal principal-id="555001"><login>09121112233</login></principal>
</results>
XML);
            }

            if (str_contains($url, 'action=permissions-update')) {
                return Http::response('<?xml version="1.0"?><results><status code="ok"/></results>');
            }

            return Http::response('<?xml version="1.0"?><results><status code="invalid"/></results>', 200);
        });

        $user = User::factory()->create([
            'mobile' => '09121112233',
            'name' => 'Student',
            'family' => 'Test',
        ]);

        $course = Course::query()->create([
            'title' => 'Course',
            'slug' => 'adobe-enroll-sync',
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'Class',
            'code' => 'ENRSYNC',
            'status' => CourseClassStatus::Upcoming,
            'delivery_type' => CourseClassDeliveryType::Online,
            'online_platform' => CourseClassOnlinePlatform::AdobeConnect,
        ]);

        AdobeRoom::query()->create([
            'course_class_id' => $class->id,
            'sco_id' => '9009',
            'url_path' => '/room/',
            'meeting_url' => 'https://connect.test/room/',
            'access_type' => AdobeRoomAccessType::Protected,
            'status' => AdobeRoomStatus::Active,
        ]);

        $enrollment = Enrollment::query()->create([
            'course_class_id' => $class->id,
            'user_id' => $user->id,
            'status' => EnrollmentStatus::Confirmed,
            'confirmed_at' => now(),
            'price_amount' => 0,
        ]);

        app(SyncAdobeConnectEnrollmentService::class)->sync($enrollment);

        $user->refresh();
        $this->assertSame('555001', $user->adobe_connect_principal_id);
    }
}
