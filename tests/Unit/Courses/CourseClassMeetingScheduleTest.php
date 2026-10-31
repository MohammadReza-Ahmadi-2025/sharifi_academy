<?php

namespace Tests\Unit\Courses;

use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Support\CourseClassMeetingSchedule;
use Tests\TestCase;

class CourseClassMeetingScheduleTest extends TestCase
{
    public function test_summary_label_combines_days_and_daily_time(): void
    {
        $class = new CourseClass([
            'meeting_weekdays' => [6, 1],
            'meeting_starts_time' => '12:00',
            'meeting_ends_time' => '14:00',
        ]);

        $meeting = CourseClassMeetingSchedule::forClass($class);

        $this->assertSame([6, 1], $meeting['weekdays']);
        $this->assertSame(['شنبه', 'دوشنبه'], $meeting['weekday_labels']);
        $this->assertSame('ساعت 12:00 تا 14:00', $meeting['time_label']);
        $this->assertSame('شنبه، دوشنبه — ساعت 12:00 تا 14:00', $meeting['summary_label']);
    }

    public function test_normalize_weekdays_orders_iranian_week(): void
    {
        $this->assertSame(
            [6, 0, 2],
            CourseClassMeetingSchedule::normalizeWeekdays([2, 6, 0, 2, 9]),
        );
    }
}
