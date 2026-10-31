<?php

namespace Tests\Feature\Instructor;

use App\Models\User;
use App\Modules\AdobeConnect\Enums\AdobeRoomAccessType;
use App\Modules\AdobeConnect\Enums\AdobeRoomStatus;
use App\Modules\Courses\Enums\ClassInstructorRole;
use App\Modules\Courses\Enums\ClassSessionStatus;
use App\Modules\Courses\Enums\CourseClassDeliveryType;
use App\Modules\Courses\Enums\CourseClassOnlinePlatform;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\Courses\Models\ClassInstructor;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Roles\Support\DefaultRolePermissions;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InstructorAdobeJoinTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        DefaultRolePermissions::sync();

        Config::set('adobe_connect.base_url', 'https://meet.example.com');
        Config::set('adobe_connect.login', 'api@example.com');
        Config::set('adobe_connect.api_token', 'secret-token');
    }

    public function test_assigned_instructor_can_join_adobe_as_host_credentials(): void
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
                return Http::response(<<<'XML'
<?xml version="1.0"?><results><status code="ok"/>
  <principal-list><principal principal-id="4242"><login>09120000002</login></principal></principal-list>
</results>
XML);
            }

            if (str_contains($url, 'action=principal-update')) {
                return Http::response('<?xml version="1.0"?><results><status code="ok"/></results>');
            }

            if (str_contains($url, 'action=permissions-update')) {
                return Http::response('<?xml version="1.0"?><results><status code="ok"/></results>');
            }

            return Http::response('<?xml version="1.0"?><results><status code="invalid"/></results>', 200);
        });

        $instructor = User::where('mobile', '09120000002')->firstOrFail();

        [$class, $session] = $this->makeClassWithSession();

        ClassInstructor::query()->create([
            'course_class_id' => $class->id,
            'user_id' => $instructor->id,
            'role' => ClassInstructorRole::Lead,
            'is_primary' => true,
        ]);

        AdobeRoom::query()->create([
            'course_class_id' => $class->id,
            'class_session_id' => $session->id,
            'sco_id' => 'sco-inst-1',
            'url_path' => '/instructor-live/',
            'meeting_url' => '',
            'access_type' => AdobeRoomAccessType::Protected,
            'status' => AdobeRoomStatus::Active,
        ]);

        $this->actingAs($instructor)
            ->get("/instructor/classes/{$class->id}/sessions/{$session->id}/join")
            ->assertRedirect('https://meet.example.com/instructor-live/?login=09120000002&password=09120000002');

        Http::assertSent(fn ($request) => str_contains($request->url(), 'permission-id=host'));
    }

    public function test_unassigned_instructor_cannot_join(): void
    {
        $instructor = User::where('mobile', '09120000002')->firstOrFail();
        [$class, $session] = $this->makeClassWithSession();

        AdobeRoom::query()->create([
            'course_class_id' => $class->id,
            'sco_id' => 'sco-inst-2',
            'url_path' => '/blocked/',
            'meeting_url' => '',
            'access_type' => AdobeRoomAccessType::Protected,
            'status' => AdobeRoomStatus::Active,
        ]);

        $this->actingAs($instructor)
            ->get("/instructor/classes/{$class->id}/sessions/{$session->id}/join")
            ->assertForbidden();
    }

    /**
     * @return array{0: CourseClass, 1: ClassSession}
     */
    private function makeClassWithSession(): array
    {
        $course = Course::query()->create([
            'title' => 'Instructor Join Course',
            'slug' => 'instructor-join-course',
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه استاد',
            'code' => 'INST-JOIN-1',
            'status' => CourseClassStatus::Upcoming,
            'delivery_type' => CourseClassDeliveryType::Online,
            'online_platform' => CourseClassOnlinePlatform::AdobeConnect,
            'price' => 0,
        ]);

        $session = ClassSession::query()->create([
            'course_class_id' => $class->id,
            'session_number' => 1,
            'title' => 'جلسه زنده',
            'status' => ClassSessionStatus::Scheduled,
            'scheduled_start_at' => now()->addHour(),
            'scheduled_end_at' => now()->addHours(3),
        ]);

        return [$class, $session];
    }
}
