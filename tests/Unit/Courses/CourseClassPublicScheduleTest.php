<?php

namespace Tests\Unit\Courses;

use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Support\CourseClassPublicSchedule;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CourseClassPublicScheduleTest extends TestCase
{
    public function test_ongoing_when_start_passed_and_end_in_future(): void
    {
        Carbon::setTestNow('2026-06-15 12:00:00');

        $class = new CourseClass([
            'starts_at' => Carbon::parse('2026-06-01 09:00:00'),
            'ends_at' => Carbon::parse('2026-07-01 18:00:00'),
        ]);

        $schedule = CourseClassPublicSchedule::forClass($class);

        $this->assertSame(CourseClassPublicSchedule::STATUS_ONGOING, $schedule['status']);
        $this->assertSame('در حال برگزاری', $schedule['status_label']);
        $this->assertNotNull($schedule['range_label']);
        $this->assertArrayHasKey('meeting', $schedule);
        $this->assertNull($schedule['meeting']['summary_label']);

        Carbon::setTestNow();
    }

    public function test_upcoming_when_start_in_future(): void
    {
        Carbon::setTestNow('2026-06-15 12:00:00');

        $class = new CourseClass([
            'starts_at' => Carbon::parse('2026-08-01 09:00:00'),
            'ends_at' => Carbon::parse('2026-09-01 18:00:00'),
        ]);

        $schedule = CourseClassPublicSchedule::forClass($class);

        $this->assertSame(CourseClassPublicSchedule::STATUS_UPCOMING, $schedule['status']);
        $this->assertSame('به‌زودی', $schedule['status_label']);

        Carbon::setTestNow();
    }
}
