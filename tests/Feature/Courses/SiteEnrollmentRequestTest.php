<?php

namespace Tests\Feature\Courses;

use App\Models\User;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Profile\Models\UserProfile;
use App\Modules\Roles\Support\DefaultRolePermissions;
use App\Support\Notifications\Messages\EnrollmentPendingAdminNotification;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SiteEnrollmentRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        DefaultRolePermissions::sync();
    }

    public function test_student_can_request_enrollment_for_public_class(): void
    {
        Notification::fake();

        $student = User::where('mobile', '09120000003')->firstOrFail();
        $admin = User::where('mobile', '09120000001')->firstOrFail();
        $this->ensureCompleteProfile($student);

        $course = Course::query()->create([
            'title' => 'Site Enroll',
            'slug' => 'site-enroll-course',
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه A',
            'code' => 'SE-A-01',
            'status' => CourseClassStatus::Upcoming,
            'price' => 2_000_000,
            'is_public' => true,
        ]);

        $this->actingAs($student)
            ->post("/courses/{$course->slug}/enrollments", [
                'course_class_id' => $class->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('enrollments', [
            'course_class_id' => $class->id,
            'user_id' => $student->id,
            'status' => EnrollmentStatus::Pending->value,
        ]);

        Notification::assertSentTo($admin, EnrollmentPendingAdminNotification::class);
    }

    public function test_incomplete_profile_cannot_request_enrollment(): void
    {
        $student = User::factory()->create([
            'name' => null,
            'family' => 'تست',
            'mobile' => '09129990001',
            'status' => 1,
        ]);
        $student->assignRole('student');

        $course = Course::query()->create([
            'title' => 'Profile block',
            'slug' => 'profile-block-course',
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه',
            'code' => 'PB-01',
            'status' => CourseClassStatus::Upcoming,
            'is_public' => true,
        ]);

        $this->actingAs($student)
            ->post("/courses/{$course->slug}/enrollments", [
                'course_class_id' => $class->id,
            ])
            ->assertForbidden();
    }

    public function test_duplicate_pending_request_is_forbidden(): void
    {
        $student = User::where('mobile', '09120000003')->firstOrFail();
        $this->ensureCompleteProfile($student);

        $course = Course::query()->create([
            'title' => 'Dup',
            'slug' => 'dup-enroll',
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه',
            'code' => 'DUP-01',
            'status' => CourseClassStatus::Upcoming,
            'is_public' => true,
        ]);

        Enrollment::query()->create([
            'course_class_id' => $class->id,
            'user_id' => $student->id,
            'status' => EnrollmentStatus::Pending,
            'price_amount' => 0,
        ]);

        $this->actingAs($student)
            ->post("/courses/{$course->slug}/enrollments", [
                'course_class_id' => $class->id,
            ])
            ->assertForbidden();
    }

    public function test_cancelled_enrollment_can_be_requested_again(): void
    {
        Notification::fake();

        $student = User::where('mobile', '09120000003')->firstOrFail();
        $this->ensureCompleteProfile($student);

        $course = Course::query()->create([
            'title' => 'Reopen',
            'slug' => 'reopen-enroll',
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه',
            'code' => 'REOPEN-01',
            'status' => CourseClassStatus::Upcoming,
            'is_public' => true,
            'price' => 1_500_000,
        ]);

        Enrollment::query()->create([
            'course_class_id' => $class->id,
            'user_id' => $student->id,
            'status' => EnrollmentStatus::Cancelled,
            'price_amount' => 0,
            'cancelled_at' => now(),
        ]);

        $this->actingAs($student)
            ->post("/courses/{$course->slug}/enrollments", [
                'course_class_id' => $class->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('enrollments', [
            'course_class_id' => $class->id,
            'user_id' => $student->id,
            'status' => EnrollmentStatus::Pending->value,
            'price_amount' => 1_500_000,
        ]);
        $this->assertSame(1, Enrollment::query()->where('course_class_id', $class->id)->where('user_id', $student->id)->count());
    }

    private function ensureCompleteProfile(User $user): void
    {
        $user->forceFill([
            'name' => $user->name ?: 'دانشجو',
            'family' => $user->family ?: 'تست',
        ])->save();

        UserProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            ['province_city_id' => 1],
        );
    }
}
