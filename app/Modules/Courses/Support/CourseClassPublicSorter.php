<?php

namespace App\Modules\Courses\Support;

use App\Modules\Courses\Models\CourseClass;
use Illuminate\Support\Collection;

final class CourseClassPublicSorter
{
    /**
     * Open registration + not finished first, then cheaper, then newer.
     *
     * @param  Collection<int, CourseClass>  $classes
     * @return Collection<int, CourseClass>
     */
    public static function sort(Collection $classes): Collection
    {
        return $classes
            ->sort(function (CourseClass $left, CourseClass $right): int {
                $leftRank = self::availabilityRank($left);
                $rightRank = self::availabilityRank($right);
                if ($leftRank !== $rightRank) {
                    return $leftRank <=> $rightRank;
                }

                $leftPrice = CourseClassPricing::effectivePrice($left);
                $rightPrice = CourseClassPricing::effectivePrice($right);
                $leftPriceKey = $leftPrice > 0 ? $leftPrice : PHP_INT_MAX;
                $rightPriceKey = $rightPrice > 0 ? $rightPrice : PHP_INT_MAX;
                if ($leftPriceKey !== $rightPriceKey) {
                    return $leftPriceKey <=> $rightPriceKey;
                }

                $leftStart = $left->starts_at?->getTimestamp() ?? 0;
                $rightStart = $right->starts_at?->getTimestamp() ?? 0;
                if ($leftStart !== $rightStart) {
                    return $rightStart <=> $leftStart;
                }

                return $right->id <=> $left->id;
            })
            ->values();
    }

    private static function availabilityRank(CourseClass $class): int
    {
        $schedule = CourseClassPublicSchedule::forClass($class);
        $finished = ($schedule['status'] ?? null) === CourseClassPublicSchedule::STATUS_FINISHED;

        if ($class->isOpenForRegistration() && ! $finished) {
            return 0;
        }

        if (! $finished) {
            return 1;
        }

        return 2;
    }
}
