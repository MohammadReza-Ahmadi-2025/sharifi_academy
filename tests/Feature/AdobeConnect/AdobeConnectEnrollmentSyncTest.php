<?php

namespace Tests\Feature\AdobeConnect;

use App\Models\User;
use App\Modules\AdobeConnect\Enums\AdobeRoomAccessType;
use App\Modules\AdobeConnect\Enums\AdobeRoomStatus;
use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\AdobeConnect\Services\SyncAdobeConnectClassInstructorsService;
use App\Modules\AdobeConnect\Services\SyncAdobeConnectEnrollmentService;
use App\Modules\Courses\Enums\ClassInstructorRole;
use App\Modules\Courses\Enums\CourseClassDeliveryType;
use App\Modules\Courses\Enums\CourseClassOnlinePlatform;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Models\ClassInstructor;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use Database\Seeders\DatabaseSeeder;
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
        $this->fakeAdobeUserCreate('09121112233', '555001');

        [$user, $class, $enrollment] = $this->makeConfirmedEnrollment('09121112233', 'ENRSYNC');

        $result = app(SyncAdobeConnectEnrollmentService::class)->sync($enrollment);

        $user->refresh();
        $this->assertSame('555001', $user->adobe_connect_principal_id);
        $this->assertContains($result->status, ['linked', 'created']);
    }

    public function test_admin_can_resync_single_and_bulk_enrollments(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->fakeAdobeUserCreate('09123334455', '777001');

        [$user, $class, $enrollment] = $this->makeConfirmedEnrollment('09123334455', 'RESYNC1');
        $course = $class->course;

        $this->actingAs($admin)
            ->post("/admin/courses/{$course->id}/classes/{$class->id}/enrollments/{$enrollment->id}/sync-adobe")
            ->assertRedirect();

        $user->refresh();
        $this->assertSame('777001', $user->adobe_connect_principal_id);

        $user->forceFill(['adobe_connect_principal_id' => null, 'adobe_connect_synced_at' => null])->save();

        $this->actingAs($admin)
            ->post("/admin/courses/{$course->id}/classes/{$class->id}/enrollments/sync-adobe", [
                'only_missing' => true,
            ])
            ->assertRedirect();

        $user->refresh();
        $this->assertSame('777001', $user->adobe_connect_principal_id);
    }

    public function test_instructor_sync_grants_host_permission(): void
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
  <principal principal-id="888001"><login>09125556677</login></principal>
</results>
XML);
            }

            if (str_contains($url, 'action=permissions-update') && str_contains($url, 'permission-id=host')) {
                return Http::response('<?xml version="1.0"?><results><status code="ok"/></results>');
            }

            return Http::response('<?xml version="1.0"?><results><status code="invalid"/></results>', 200);
        });

        $instructor = User::factory()->create([
            'mobile' => '09125556677',
            'name' => 'استاد',
            'family' => 'تست',
        ]);

        $course = Course::query()->create([
            'title' => 'Instructor Sync',
            'slug' => 'instructor-adobe-sync',
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'Class',
            'code' => 'INSTSYNC',
            'status' => CourseClassStatus::Upcoming,
            'delivery_type' => CourseClassDeliveryType::Online,
            'online_platform' => CourseClassOnlinePlatform::AdobeConnect,
        ]);

        AdobeRoom::query()->create([
            'course_class_id' => $class->id,
            'sco_id' => '9010',
            'url_path' => '/host-room/',
            'meeting_url' => 'https://connect.test/host-room/',
            'access_type' => AdobeRoomAccessType::Protected,
            'status' => AdobeRoomStatus::Active,
        ]);

        ClassInstructor::query()->create([
            'course_class_id' => $class->id,
            'user_id' => $instructor->id,
            'role' => ClassInstructorRole::Lead,
            'is_primary' => true,
        ]);

        $stats = app(SyncAdobeConnectClassInstructorsService::class)->sync($class->fresh());

        $instructor->refresh();
        $this->assertSame('888001', $instructor->adobe_connect_principal_id);
        $this->assertSame(1, $stats['created'] + $stats['linked']);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'permission-id=host'));
    }

    private function fakeAdobeUserCreate(string $login, string $principalId): void
    {
        Http::fake(function ($request) use ($login, $principalId) {
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
                return Http::response(<<<XML
<?xml version="1.0"?><results><status code="ok"/>
  <principal principal-id="{$principalId}"><login>{$login}</login></principal>
</results>
XML);
            }

            if (str_contains($url, 'action=permissions-update')) {
                return Http::response('<?xml version="1.0"?><results><status code="ok"/></results>');
            }

            return Http::response('<?xml version="1.0"?><results><status code="invalid"/></results>', 200);
        });
    }

    /**
     * @return array{0: User, 1: CourseClass, 2: Enrollment}
     */
    private function makeConfirmedEnrollment(string $mobile, string $code): array
    {
        $user = User::factory()->create([
            'mobile' => $mobile,
            'name' => 'Student',
            'family' => 'Test',
        ]);

        $course = Course::query()->create([
            'title' => 'Course '.$code,
            'slug' => 'adobe-enroll-'.strtolower($code),
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'Class',
            'code' => $code,
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

        return [$user, $class, $enrollment];
    }
}
