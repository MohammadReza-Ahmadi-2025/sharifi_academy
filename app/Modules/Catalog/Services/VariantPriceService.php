<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\Price;
use App\Modules\Catalog\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class VariantPriceService
{
    public function applyAdminPrice(ProductVariant $variant, int $price, int $finalPrice): Price
    {
        return $this->applyAdminVariantPricing($variant, [
            'price' => $price,
            'final_price' => $finalPrice,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function applyAdminVariantPricing(ProductVariant $variant, array $data): Price
    {
        $price = max(0, (int) ($data['price'] ?? 0));
        $discountType = filled($data['discount_type'] ?? null) ? (string) $data['discount_type'] : null;
        $discountValue = isset($data['discount_value']) && $data['discount_value'] !== ''
            ? max(0, (int) $data['discount_value'])
            : null;

        $finalPrice = $price;

        if ($discountType && $discountValue !== null && $discountValue > 0) {
            if ($discountType === 'percentage') {
                $finalPrice = (int) ceil($price - ($price * $discountValue / 100));
            } elseif ($discountType === 'fixed') {
                $finalPrice = max(0, $price - $discountValue);
            }
        } elseif (isset($data['final_price']) && $data['final_price'] !== '' && (int) $data['final_price'] > 0) {
            $finalPrice = max(0, (int) $data['final_price']);
        }

        $current = $variant->currentPrice;

        if ($current !== null && $this->pricingMatches($current, $price, $finalPrice, $discountType, $discountValue)) {
            return $current;
        }

        return DB::transaction(function () use ($variant, $price, $finalPrice, $discountType, $discountValue) {
            $this->closeOpenPrices($variant->id);

            return $this->createPriceRow($variant->id, $price, $finalPrice, $discountType, $discountValue);
        });
    }

    private function pricingMatches(
        Price $current,
        int $price,
        int $finalPrice,
        ?string $discountType,
        ?int $discountValue,
    ): bool {
        return (int) $current->price === $price
            && (int) $current->final_price === $finalPrice
            && ($current->discount_type ?? null) === $discountType
            && (int) ($current->discount_value ?? 0) === (int) ($discountValue ?? 0);
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

    private function createPriceRow(
        int $variantId,
        int $price,
        int $finalPrice,
        ?string $discountType = null,
        ?int $discountValue = null,
    ): Price {
        $attributes = [
            'variant_id' => $variantId,
            'price' => $price,
            'final_price' => $finalPrice,
            'starts_at' => now(),
            'ends_at' => null,
        ];

        if ($discountType && $discountValue !== null && $discountValue > 0) {
            $attributes['discount_type'] = $discountType;
            $attributes['discount_value'] = $discountValue;
        } elseif ($finalPrice < $price && $price > 0) {
            $attributes['discount_type'] = 'percentage';
            $attributes['discount_value'] = (int) round((($price - $finalPrice) / $price) * 100);
        }

        return Price::query()->create($attributes);
    }
}
