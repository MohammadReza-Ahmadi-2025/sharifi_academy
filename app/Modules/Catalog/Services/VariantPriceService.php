<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\Price;
use App\Modules\Catalog\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class VariantPriceService
{
    public function applyAdminPrice(ProductVariant $variant, int $price, int $finalPrice): Price
    {
        $price = max(0, $price);
        $finalPrice = $finalPrice > 0 ? max(0, $finalPrice) : $price;

        $current = $variant->currentPrice;

        if ($current !== null && $this->amountsMatch($current, $price, $finalPrice)) {
            return $current;
        }

        return DB::transaction(function () use ($variant, $price, $finalPrice) {
            $this->closeOpenPrices($variant->id);

            return $this->createPriceRow($variant->id, $price, $finalPrice);
        });
    }

    private function amountsMatch(Price $current, int $price, int $finalPrice): bool
    {
        return (int) $current->price === $price
            && (int) $current->final_price === $finalPrice;
    }

    private function closeOpenPrices(int $variantId): void
    {
        Price::query()
            ->where('variant_id', $variantId)
            ->where(function ($query): void {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->update(['ends_at' => now()]);
    }

    private function createPriceRow(int $variantId, int $price, int $finalPrice): Price
    {
        $attributes = [
            'variant_id' => $variantId,
            'price' => $price,
            'final_price' => $finalPrice,
            'starts_at' => now(),
            'ends_at' => null,
        ];

        if ($finalPrice < $price && $price > 0) {
            $attributes['discount_type'] = 'percentage';
            $attributes['discount_value'] = round((($price - $finalPrice) / $price) * 100, 2);
        }

        return Price::query()->create($attributes);
    }
}
