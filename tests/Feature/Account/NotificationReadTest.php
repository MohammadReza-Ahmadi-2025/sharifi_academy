<?php

namespace Tests\Feature\Account;

use App\Models\User;
use App\Support\Notifications\Messages\EnrollmentConfirmedNotification;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_mark_notification_as_read(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = User::where('mobile', '09120000003')->firstOrFail();

        $course = Course::query()->create([
            'title' => 'N',
            'slug' => 'n-course',
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'C',
            'code' => 'NC-01',
            'status' => CourseClassStatus::Upcoming,
            'is_public' => true,
        ]);

        $enrollment = Enrollment::query()->create([
            'course_class_id' => $class->id,
            'user_id' => $student->id,
            'status' => EnrollmentStatus::Confirmed,
            'confirmed_at' => now(),
            'price_amount' => 0,
        ]);

        $student->notify(new EnrollmentConfirmedNotification($enrollment));

        $notification = $student->unreadNotifications()->first();
        $this->assertNotNull($notification);

        $this->actingAs($student)
            ->post("/account/notifications/{$notification->id}/read")
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }
}
