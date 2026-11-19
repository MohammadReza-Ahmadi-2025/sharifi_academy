<?php

namespace App\Modules\Courses\Support;

use App\Modules\Courses\Models\CourseClass;

final class CourseClassPricing
{
    public static function listPrice(CourseClass $class): int
    {
        return max(0, (int) ($class->price ?? 0));
    }

    public static function discountPercent(CourseClass $class): int
    {
        return max(0, min(100, (int) ($class->discount_percent ?? 0)));
    }

    public static function hasDiscount(CourseClass $class): bool
    {
        return self::discountPercent($class) > 0 && self::listPrice($class) > 0;
    }

    /**
     * Chargeable tuition after percentage discount (catalog-aligned ceil).
     */
    public static function effectivePrice(CourseClass $class): int
    {
        $price = self::listPrice($class);
        $percent = self::discountPercent($class);

        if ($price <= 0 || $percent <= 0) {
            return $price;
        }

        if ($percent >= 100) {
            return 0;
        }

        return (int) ceil($price - ($price * $percent / 100));
    }

    public static function chargeableAmount(CourseClass $class): int
    {
        return self::effectivePrice($class);
    }

    /**
     * SQL expression for effective price of alias (integer ceil, DB-portable).
     */
    public static function sqlEffectiveExpression(string $alias = 'cc'): string
    {
        return "CASE WHEN COALESCE({$alias}.discount_percent, 0) > 0"
            ." THEN CAST((({$alias}.price * (100 - {$alias}.discount_percent)) + 99) / 100 AS INTEGER)"
            ." ELSE {$alias}.price END";
    }

    public static function sqlMinPublicEffectiveSubquery(string $courseIdColumn = 'courses.id'): string
    {
        $expr = self::sqlEffectiveExpression('cc');

        return "(SELECT MIN({$expr}) FROM course_classes cc"
            ." WHERE cc.course_id = {$courseIdColumn}"
            .' AND cc.is_public = 1 AND cc.deleted_at IS NULL)';
    }
}
