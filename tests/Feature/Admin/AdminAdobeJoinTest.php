<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\AdobeConnect\Enums\AdobeRoomAccessType;
use App\Modules\AdobeConnect\Enums\AdobeRoomStatus;
use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\Courses\Enums\CourseClassDeliveryType;
use App\Modules\Courses\Enums\CourseClassOnlinePlatform;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Roles\Support\DefaultRolePermissions;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\Support\FakesAdobeConnectHttp;
use Tests\TestCase;

class AdminAdobeJoinTest extends TestCase
{
    use FakesAdobeConnectHttp;
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

    public function test_admin_join_grants_host_and_redirects_with_credentials(): void
    {
        Http::fake(function ($request) {
            $url = $request->url();
            if (($auth = $this->adobeAuthResponse($url)) !== null) {
                return $auth;
            }

            if (str_contains($url, 'action=principal-list')) {
                return Http::response(<<<'XML'
<?xml version="1.0"?><results><status code="ok"/>
  <principal-list><principal principal-id="5151"><login>09120000001</login></principal></principal-list>
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

        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $course = Course::query()->create([
            'title' => 'Admin Join Course',
            'slug' => 'admin-join-course',
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه ادمین',
            'code' => 'ADM-JOIN-1',
            'status' => CourseClassStatus::Upcoming,
            'delivery_type' => CourseClassDeliveryType::Online,
            'online_platform' => CourseClassOnlinePlatform::AdobeConnect,
            'price' => 0,
        ]);

        AdobeRoom::query()->create([
            'course_class_id' => $class->id,
            'sco_id' => 'sco-admin-1',
            'url_path' => '/academy-class-admin-join/',
            'meeting_url' => '',
            'access_type' => AdobeRoomAccessType::Protected,
            'status' => AdobeRoomStatus::Active,
        ]);

        $this->actingAs($admin)
            ->get("/admin/classes/{$class->id}/join")
            ->assertRedirect('https://meet.example.com/academy-class-admin-join/?login=09120000001&password=09120000001');

        Http::assertSent(fn ($request) => str_contains($request->url(), 'permission-id=host'));
    }

    public function test_admin_class_edit_exposes_authenticated_join_url(): void
    {
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $course = Course::query()->create([
            'title' => 'Admin Edit Join',
            'slug' => 'admin-edit-join',
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'کلاس',
            'code' => 'ADM-EDIT-JOIN',
            'status' => CourseClassStatus::Upcoming,
            'delivery_type' => CourseClassDeliveryType::Online,
            'online_platform' => CourseClassOnlinePlatform::AdobeConnect,
            'price' => 0,
        ]);

        AdobeRoom::query()->create([
            'course_class_id' => $class->id,
            'sco_id' => 'sco-admin-edit',
            'url_path' => '/edit-join/',
            'meeting_url' => 'https://meet.example.com/edit-join/',
            'access_type' => AdobeRoomAccessType::Protected,
            'status' => AdobeRoomStatus::Active,
        ]);

        $this->actingAs($admin)
            ->get("/admin/courses/{$course->id}/classes/{$class->id}/edit")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Courses/Classes/Edit')
                ->where('class_join_url', "/admin/classes/{$class->id}/join"));
    }
}
