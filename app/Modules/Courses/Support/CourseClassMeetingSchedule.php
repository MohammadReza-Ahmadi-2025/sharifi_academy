<?php

namespace App\Modules\Courses\Support;

use App\Modules\Courses\Models\CourseClass;
use Carbon\Carbon;

/**
 * برنامهٔ هفتگی کلاس: روزهای هفته + ساعت شروع/پایان هر جلسهٔ روزانه.
 * روزها با اعداد PHP date('w') ذخیره می‌شوند: ۰=یکشنبه … ۶=شنبه.
 */
final class CourseClassMeetingSchedule
{
    /**
     * ترتیب نمایش هفتهٔ ایرانی (از شنبه).
     *
     * @var list<int>
     */
    public const WEEK_ORDER = [6, 0, 1, 2, 3, 4, 5];

    /**
     * @return array<int, string>
     */
    public static function weekdayLabels(): array
    {
        return [
            6 => 'شنبه',
            0 => 'یکشنبه',
            1 => 'دوشنبه',
            2 => 'سه‌شنبه',
            3 => 'چهارشنبه',
            4 => 'پنجشنبه',
            5 => 'جمعه',
        ];
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    public static function weekdayOptions(): array
    {
        $labels = self::weekdayLabels();

        return array_map(
            static fn (int $day) => ['value' => $day, 'label' => $labels[$day]],
            self::WEEK_ORDER,
        );
    }

    /**
     * @param  list<int|string>|null  $weekdays
     * @return list<int>
     */
    public static function normalizeWeekdays(?array $weekdays): array
    {
        if ($weekdays === null || $weekdays === []) {
            return [];
        }

        $normalized = [];
        foreach ($weekdays as $day) {
            if (! is_numeric($day)) {
                continue;
            }
            $value = (int) $day;
            if ($value < 0 || $value > 6) {
                continue;
            }
            $normalized[$value] = $value;
        }

        $ordered = [];
        foreach (self::WEEK_ORDER as $day) {
            if (isset($normalized[$day])) {
                $ordered[] = $day;
            }
        }

        return $ordered;
    }

    public static function normalizeTime(mixed $time): ?string
    {
        if ($time === null || $time === '') {
            return null;
        }

        if (! is_string($time) && ! is_numeric($time)) {
            return null;
        }

        $raw = trim((string) $time);
        if ($raw === '') {
            return null;
        }

        try {
            return Carbon::parse($raw)->format('H:i');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array{
     *     weekdays: list<int>,
     *     weekday_labels: list<string>,
     *     starts_time: string|null,
     *     ends_time: string|null,
     *     time_label: string|null,
     *     days_label: string|null,
     *     summary_label: string|null
     * }
     */
    public static function forClass(CourseClass $class): array
    {
        $weekdays = self::normalizeWeekdays(
            is_array($class->meeting_weekdays) ? $class->meeting_weekdays : null,
        );
        $labels = self::weekdayLabels();
        $weekdayLabels = array_map(static fn (int $day) => $labels[$day], $weekdays);

        $starts = self::normalizeTime($class->meeting_starts_time);
        $ends = self::normalizeTime($class->meeting_ends_time);

        $timeLabel = null;
        if ($starts !== null && $ends !== null) {
            $timeLabel = 'ساعت '.$starts.' تا '.$ends;
        } elseif ($starts !== null) {
            $timeLabel = 'از ساعت '.$starts;
        } elseif ($ends !== null) {
            $timeLabel = 'تا ساعت '.$ends;
        }

        $daysLabel = $weekdayLabels === [] ? null : implode('، ', $weekdayLabels);

        $summary = null;
        if ($daysLabel !== null && $timeLabel !== null) {
            $summary = $daysLabel.' — '.$timeLabel;
        } elseif ($daysLabel !== null) {
            $summary = $daysLabel;
        } elseif ($timeLabel !== null) {
            $summary = $timeLabel;
        }

        return [
            'weekdays' => $weekdays,
            'weekday_labels' => $weekdayLabels,
            'starts_time' => $starts,
            'ends_time' => $ends,
            'time_label' => $timeLabel,
            'days_label' => $daysLabel,
            'summary_label' => $summary,
        ];
    }
}
