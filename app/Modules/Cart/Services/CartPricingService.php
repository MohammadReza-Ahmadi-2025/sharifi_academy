<?php

namespace App\Modules\Cart\Services;

use App\Modules\Catalog\Models\ProductVariant;

class CartPricingService
{
    /**
     * @return array{unit: int, compare_at: ?int}
     */
    public function resolveSellPrice(ProductVariant $variant, int $quantity = 1, ?int $userId = null): array
    {
        $price = $variant->currentPrice;
        $regularUnit = (int) ($price?->final_price ?? $price?->price ?? 0);
        $listCompare = ($price && $price->has_discount && $price->price > $regularUnit)
            ? (int) $price->price
            : null;

        return [
            'unit' => $regularUnit,
            'compare_at' => $listCompare,
        ];
    }

    /**
     * @return array{compare_unit: ?int, discount_percent: int, discount_label: ?string}
     */
    public function cartLineDiscountMeta(
        ProductVariant $variant,
        int $quantity,
        int $unitPrice,
        ?int $userId,
    ): array {
        $sell = $this->resolveSellPrice($variant, $quantity, $userId);
        $compareUnit = $sell['compare_at'];

        if ($compareUnit === null) {
            $catalog = $variant->currentPrice;
            $regularUnit = (int) ($catalog?->final_price ?? $catalog?->price ?? 0);

            if ($catalog && $catalog->has_discount && (int) $catalog->price > $regularUnit) {
                $compareUnit = (int) $catalog->price;
            }
        }

        $compareUnit = ($compareUnit !== null && $compareUnit > $unitPrice) ? $compareUnit : null;

        $discountPercent = $compareUnit
            ? (int) round((($compareUnit - $unitPrice) / $compareUnit) * 100)
            : 0;

        $discountLabel = $discountPercent > 0 ? 'تخفیف' : null;

        return [
            'compare_unit' => $compareUnit,
            'discount_percent' => $discountPercent,
            'discount_label' => $discountLabel,
        ];
    }
}
