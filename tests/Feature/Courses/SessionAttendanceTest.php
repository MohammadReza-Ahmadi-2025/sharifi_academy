<?php

namespace Tests\Feature\Courses;

use App\Models\User;
use App\Modules\Courses\Enums\ClassSessionStatus;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Enums\SessionAttendanceRole;
use App\Modules\Courses\Enums\SessionAttendanceSource;
use App\Modules\Courses\Enums\SessionAttendanceStatus;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\SessionAttendance;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SessionAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_attendance_table_and_relations(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertTrue(Schema::hasTable('session_attendances'));

        $admin = User::where('mobile', '09120000001')->firstOrFail();
        $student = User::where('mobile', '09120000003')->firstOrFail();

        $course = Course::query()->create([
            'title' => 'حضور و غیاب',
            'slug' => 'attendance-test',
            'status' => CourseStatus::Published,
            'created_by' => $admin->id,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه ۱',
            'code' => 'ATT-1',
            'status' => CourseClassStatus::Upcoming,
            'created_by' => $admin->id,
        ]);

        $start = now()->addDay()->startOfHour();

        $session = ClassSession::query()->create([
            'course_class_id' => $class->id,
            'session_number' => 1,
            'status' => ClassSessionStatus::Scheduled,
            'scheduled_start_at' => $start,
            'scheduled_end_at' => $start->copy()->addHours(2),
            'created_by' => $admin->id,
        ]);

        $attendance = SessionAttendance::query()->create([
            'class_session_id' => $session->id,
            'user_id' => $student->id,
            'status' => SessionAttendanceStatus::Present,
            'role' => SessionAttendanceRole::Student,
            'source' => SessionAttendanceSource::Manual,
            'first_joined_at' => $start,
            'duration_minutes' => 90,
            'note' => 'حاضر در کلاس',
        ]);

        $session->load('attendances.user');
        $student->load('sessionAttendances.classSession');

        $this->assertCount(1, $session->attendances);
        $this->assertSame($student->id, $session->attendances->first()?->user_id);
        $this->assertSame('حاضر', $attendance->status_label);
        $this->assertSame($session->id, $student->sessionAttendances->first()?->class_session_id);

        $this->expectException(QueryException::class);

        SessionAttendance::query()->create([
            'class_session_id' => $session->id,
            'user_id' => $student->id,
            'status' => SessionAttendanceStatus::Absent,
        ]);
    }
}
