<?php

namespace App\Modules\Catalog\Support;

use Illuminate\Http\Request;

class CategoryBrowsePriceFilter
{
    /**
     * @return array{0: int|null, 1: int|null}
     */
    public static function boundsFromRequest(Request $request): array
    {
        $min = self::parseAmount($request->query('price_min'));
        $max = self::parseAmount($request->query('price_max'));

        if ($min !== null && $max !== null && $max < $min) {
            [$min, $max] = [$max, $min];
        }

        return [$min, $max];
    }

    /**
     * @return array{price_min: int|null, price_max: int|null}
     */
    public static function stateFromRequest(Request $request): array
    {
        [$min, $max] = self::boundsFromRequest($request);

        return [
            'price_min' => $min,
            'price_max' => $max,
        ];
    }

    private static function parseAmount(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        $amount = (int) $value;

        return $amount > 0 ? $amount : null;
    }
}
