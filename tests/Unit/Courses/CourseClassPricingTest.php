<?php

namespace Tests\Unit\Courses;

use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Support\CourseClassPricing;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CourseClassPricingTest extends TestCase
{
    #[DataProvider('effectivePriceProvider')]
    public function test_effective_price(int $price, int $discountPercent, int $expected): void
    {
        $class = new CourseClass([
            'price' => $price,
            'discount_percent' => $discountPercent,
        ]);

        $this->assertSame($expected, CourseClassPricing::effectivePrice($class));
    }

    /**
     * @return array<string, array{0: int, 1: int, 2: int}>
     */
    public static function effectivePriceProvider(): array
    {
        return [
            'no discount' => [2_000_000, 0, 2_000_000],
            'ten percent' => [1_000_000, 10, 900_000],
            'rounds up' => [100, 33, 67],
            'full discount' => [500_000, 100, 0],
            'zero price' => [0, 10, 0],
        ];
    }

    public function test_sql_effective_expression_uses_driver_cast_type(): void
    {
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $expr = CourseClassPricing::sqlEffectiveExpression('cc');

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $this->assertStringContainsString('AS SIGNED', $expr);
            $this->assertStringNotContainsString('AS INTEGER', $expr);
        } else {
            $this->assertStringContainsString('AS INTEGER', $expr);
        }
    }
}
