<?php

namespace Tests\Feature\AdobeConnect;

use App\Models\User;
use App\Modules\AdobeConnect\Enums\AdobeRoomAccessType;
use App\Modules\AdobeConnect\Enums\AdobeRoomStatus;
use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\AdobeConnect\Services\SyncAdobeSessionAttendanceService;
use App\Modules\Courses\Enums\ClassInstructorRole;
use App\Modules\Courses\Enums\ClassSessionStatus;
use App\Modules\Courses\Enums\CourseClassDeliveryType;
use App\Modules\Courses\Enums\CourseClassOnlinePlatform;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Enums\SessionAttendanceStatus;
use App\Modules\Courses\Models\ClassInstructor;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Courses\Models\SessionAttendance;
use App\Modules\Roles\Support\DefaultRolePermissions;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdobeConnectAttendanceSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        DefaultRolePermissions::sync();

        Config::set('adobe_connect.base_url', 'https://connect.test');
        Config::set('adobe_connect.login', 'api@example.com');
        Config::set('adobe_connect.api_token', 'secret-token');
    }

    public function test_sync_marks_present_and_absent_from_adobe_report(): void
    {
        $present = User::factory()->create([
            'mobile' => '09121110001',
            'name' => 'حاضر',
            'family' => 'تست',
            'adobe_connect_principal_id' => '9001',
        ]);
        $absent = User::factory()->create([
            'mobile' => '09121110002',
            'name' => 'غایب',
            'family' => 'تست',
        ]);

        [$class, $session] = $this->makeAdobeSession();

        foreach ([$present, $absent] as $user) {
            Enrollment::query()->create([
                'course_class_id' => $class->id,
                'user_id' => $user->id,
                'status' => EnrollmentStatus::Confirmed,
                'confirmed_at' => now(),
                'price_amount' => 0,
            ]);
        }

        Http::fake(function ($request) {
            $url = $request->url();
            if (str_contains($url, 'action=login')) {
                return Http::response(
                    '<?xml version="1.0"?><results><status code="ok"/></results>',
                    200,
                    ['Set-Cookie' => 'BREEZESESSION=fake-session; Path=/'],
                );
            }

            if (str_contains($url, 'action=report-meeting-attendance')) {
                return Http::response(<<<'XML'
<?xml version="1.0"?><results><status code="ok"/>
  <report-meeting-attendance>
    <row transcript-id="1" sco-id="555" principal-id="9001">
      <login>09121110001</login>
      <date-created>2026-10-30T10:00:00.000+03:30</date-created>
      <date-end>2026-10-30T11:15:00.000+03:30</date-end>
      <participant-name>حاضر تست</participant-name>
    </row>
  </report-meeting-attendance>
</results>
XML);
            }

            return Http::response('<?xml version="1.0"?><results><status code="invalid"/></results>', 200);
        });

        $stats = app(SyncAdobeSessionAttendanceService::class)->sync($session);

        $this->assertSame(1, $stats['present']);
        $this->assertSame(1, $stats['absent']);

        $this->assertDatabaseHas('session_attendances', [
            'class_session_id' => $session->id,
            'user_id' => $present->id,
            'status' => SessionAttendanceStatus::Present->value,
            'duration_minutes' => 75,
            'source' => 'system',
        ]);

        $this->assertDatabaseHas('session_attendances', [
            'class_session_id' => $session->id,
            'user_id' => $absent->id,
            'status' => SessionAttendanceStatus::Absent->value,
            'source' => 'system',
        ]);
    }

    public function test_admin_can_trigger_adobe_attendance_sync_endpoint(): void
    {
        $admin = User::where('mobile', '09120000001')->firstOrFail();
        $student = User::factory()->create(['mobile' => '09127770001', 'adobe_connect_principal_id' => '7001']);

        [$class, $session] = $this->makeAdobeSession();

        Enrollment::query()->create([
            'course_class_id' => $class->id,
            'user_id' => $student->id,
            'status' => EnrollmentStatus::Confirmed,
            'confirmed_at' => now(),
            'price_amount' => 0,
        ]);

        Http::fake(function ($request) {
            $url = $request->url();
            if (str_contains($url, 'action=login')) {
                return Http::response(
                    '<?xml version="1.0"?><results><status code="ok"/></results>',
                    200,
                    ['Set-Cookie' => 'BREEZESESSION=fake-session; Path=/'],
                );
            }

            if (str_contains($url, 'action=report-meeting-attendance')) {
                return Http::response(<<<'XML'
<?xml version="1.0"?><results><status code="ok"/>
  <report-meeting-attendance>
    <row transcript-id="2" sco-id="555" principal-id="7001">
      <login>09127770001</login>
      <date-created>2026-10-30T10:00:00.000+03:30</date-created>
      <date-end>2026-10-30T10:40:00.000+03:30</date-end>
    </row>
  </report-meeting-attendance>
</results>
XML);
            }

            return Http::response('<?xml version="1.0"?><results><status code="invalid"/></results>', 200);
        });

        $this->actingAs($admin)
            ->post("/admin/session-attendances/{$session->id}/sync-adobe", ['mark_absent' => true])
            ->assertRedirect();

        $this->assertSame(
            SessionAttendanceStatus::Present,
            SessionAttendance::query()->where('user_id', $student->id)->firstOrFail()->status,
        );
    }

    public function test_assigned_instructor_can_trigger_adobe_attendance_sync(): void
    {
        $instructor = User::where('mobile', '09120000002')->firstOrFail();
        $student = User::factory()->create(['mobile' => '09128880001', 'adobe_connect_principal_id' => '8001']);

        [$class, $session] = $this->makeAdobeSession();

        ClassInstructor::query()->create([
            'course_class_id' => $class->id,
            'user_id' => $instructor->id,
            'role' => ClassInstructorRole::Lead,
            'is_primary' => true,
        ]);

        Enrollment::query()->create([
            'course_class_id' => $class->id,
            'user_id' => $student->id,
            'status' => EnrollmentStatus::Confirmed,
            'confirmed_at' => now(),
            'price_amount' => 0,
        ]);

        Http::fake(function ($request) {
            $url = $request->url();
            if (str_contains($url, 'action=login')) {
                return Http::response(
                    '<?xml version="1.0"?><results><status code="ok"/></results>',
                    200,
                    ['Set-Cookie' => 'BREEZESESSION=fake-session; Path=/'],
                );
            }

            if (str_contains($url, 'action=report-meeting-attendance')) {
                return Http::response('<?xml version="1.0"?><results><status code="ok"/><report-meeting-attendance/></results>');
            }

            return Http::response('<?xml version="1.0"?><results><status code="invalid"/></results>', 200);
        });

        $this->actingAs($instructor)
            ->post("/instructor/session-attendances/{$session->id}/sync-adobe")
            ->assertRedirect();
    }

    /**
     * @return array{0: CourseClass, 1: ClassSession}
     */
    private function makeAdobeSession(): array
    {
        $course = Course::query()->create([
            'title' => 'Attendance Sync',
            'slug' => 'attendance-sync-'.uniqid(),
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'کلاس',
            'code' => 'ATT-'.strtoupper(substr(uniqid(), -6)),
            'status' => CourseClassStatus::Ongoing,
            'delivery_type' => CourseClassDeliveryType::Online,
            'online_platform' => CourseClassOnlinePlatform::AdobeConnect,
            'price' => 0,
        ]);

        $session = ClassSession::query()->create([
            'course_class_id' => $class->id,
            'session_number' => 1,
            'title' => 'جلسه ۱',
            'status' => ClassSessionStatus::Scheduled,
            'scheduled_start_at' => now()->setTime(10, 0),
            'scheduled_end_at' => now()->setTime(12, 0),
        ]);

        AdobeRoom::query()->create([
            'course_class_id' => $class->id,
            'sco_id' => '555',
            'url_path' => '/att-room/',
            'meeting_url' => 'https://connect.test/att-room/',
            'access_type' => AdobeRoomAccessType::Protected,
            'status' => AdobeRoomStatus::Active,
        ]);

        return [$class, $session];
    }
}
