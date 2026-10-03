<?php

namespace Tests\Feature\Courses;

use App\Models\User;
use App\Modules\Courses\Enums\ClassSessionStatus;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassSessionMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_class_can_have_ordered_sessions(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $course = Course::query()->create([
            'title' => 'لاراول',
            'slug' => 'laravel-sessions',
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه A',
            'code' => 'LAR-A-SES',
            'status' => CourseClassStatus::Upcoming,
        ]);

        $start = now()->addDays(3)->startOfHour();

        ClassSession::query()->create([
            'course_class_id' => $class->id,
            'session_number' => 2,
            'title' => 'Eloquent',
            'status' => ClassSessionStatus::Scheduled,
            'scheduled_start_at' => $start->copy()->addDay(),
            'scheduled_end_at' => $start->copy()->addDay()->addHours(2),
            'created_by' => $admin->id,
        ]);

        ClassSession::query()->create([
            'course_class_id' => $class->id,
            'session_number' => 1,
            'title' => 'مقدمات',
            'status' => ClassSessionStatus::Scheduled,
            'scheduled_start_at' => $start,
            'scheduled_end_at' => $start->copy()->addHours(2),
            'created_by' => $admin->id,
        ]);

        $sessions = $class->fresh()->classSessions;

        $this->assertCount(2, $sessions);
        $this->assertSame(1, $sessions->first()?->session_number);
        $this->assertSame('مقدمات', $sessions->first()?->title);
        $this->assertSame(ClassSessionStatus::Scheduled, $sessions->first()?->status);
        $this->assertSame('Asia/Tehran', $sessions->first()?->timezone);
        $this->assertCount(2, $admin->createdClassSessions);
    }

    public function test_session_number_must_be_unique_per_class(): void
    {
        $this->seed(DatabaseSeeder::class);

        $course = Course::query()->create([
            'title' => 'PHP',
            'slug' => 'php-sessions-unique',
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه X',
            'code' => 'PHP-X-01',
            'status' => CourseClassStatus::Upcoming,
        ]);

        $start = now()->addWeek();

        ClassSession::query()->create([
            'course_class_id' => $class->id,
            'session_number' => 1,
            'status' => ClassSessionStatus::Scheduled,
            'scheduled_start_at' => $start,
            'scheduled_end_at' => $start->copy()->addHours(2),
        ]);

        $this->expectException(QueryException::class);

        ClassSession::query()->create([
            'course_class_id' => $class->id,
            'session_number' => 1,
            'status' => ClassSessionStatus::Scheduled,
            'scheduled_start_at' => $start->copy()->addDay(),
            'scheduled_end_at' => $start->copy()->addDay()->addHours(2),
        ]);
    }
}
