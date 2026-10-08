<?php

namespace App\Modules\Courses\Support;

use App\Modules\Courses\Models\CourseClass;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class CourseClassPublicSchedule
{
    public const STATUS_UNSCHEDULED = 'unscheduled';

    public const STATUS_UPCOMING = 'upcoming';

    public const STATUS_ONGOING = 'ongoing';

    public const STATUS_FINISHED = 'finished';

    /**
     * @return array{
     *     status: string,
     *     status_label: string,
     *     range_label: string|null,
     *     starts_at: string|null,
     *     ends_at: string|null
     * }
     */
    public static function forClass(CourseClass $class): array
    {
        $start = $class->starts_at;
        $end = $class->ends_at;
        $now = Carbon::now();

        $rangeLabel = self::formatRangeLabel($start, $end);

        if ($start === null && $end === null) {
            return self::payload($class, self::STATUS_UNSCHEDULED, 'زمان‌بندی اعلام نشده', null);
        }

        if ($end !== null && $now->gt($end)) {
            return self::payload($class, self::STATUS_FINISHED, 'پایان یافته', $rangeLabel);
        }

        if ($start !== null && $now->gte($start) && ($end === null || $now->lte($end))) {
            return self::payload($class, self::STATUS_ONGOING, 'در حال برگزاری', $rangeLabel);
        }

        if ($start !== null && $now->lt($start)) {
            return self::payload($class, self::STATUS_UPCOMING, 'به‌زودی', $rangeLabel);
        }

        if ($end !== null && $now->lte($end)) {
            return self::payload($class, self::STATUS_ONGOING, 'در حال برگزاری', $rangeLabel);
        }

        return self::payload($class, self::STATUS_UNSCHEDULED, 'زمان‌بندی اعلام نشده', $rangeLabel);
    }

    /**
     * @return array{
     *     status: string,
     *     status_label: string,
     *     range_label: string|null,
     *     starts_at: string|null,
     *     ends_at: string|null
     * }
     */
    private static function payload(
        CourseClass $class,
        string $status,
        string $statusLabel,
        ?string $rangeLabel,
    ): array {
        return [
            'status' => $status,
            'status_label' => $statusLabel,
            'range_label' => $rangeLabel,
            'starts_at' => $class->starts_at?->toIso8601String(),
            'ends_at' => $class->ends_at?->toIso8601String(),
        ];
    }

    private static function formatRangeLabel(?CarbonInterface $start, ?CarbonInterface $end): ?string
    {
        if ($start === null && $end === null) {
            return null;
        }

        if ($start !== null && $end !== null) {
            return 'از '.self::formatDateTime($start).' تا '.self::formatDateTime($end);
        }

        if ($start !== null) {
            return 'از '.self::formatDateTime($start);
        }

        return 'تا '.self::formatDateTime($end);
    }

    private static function formatDateTime(CarbonInterface $date): string
    {
        return $date->copy()->locale('fa')->translatedFormat('j F Y — H:i');
    }
}
