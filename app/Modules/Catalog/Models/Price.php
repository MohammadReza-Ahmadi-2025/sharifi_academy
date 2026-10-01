<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Catalog\Enums\VariantDisplayMode;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'variant_id',
    'price',
    'final_price',
    'sale_start',
    'sale_end',
    'starts_at',
    'discount_type',
    'discount_value',
    'ends_at',
    'currency',
])]
class Price extends Model
{
    protected function casts(): array
    {
        return [
            'sale_start' => 'datetime',
            'sale_end' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'price' => 'integer',
            'final_price' => 'integer',
            'discount_value' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<ProductVariant, $this>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * @param  Builder<Price>  $query
     * @return Builder<Price>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where(function (Builder $activeQuery): void {
                $activeQuery->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $activeQuery): void {
                $activeQuery->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    public function getIsSaleActiveAttribute(): bool
    {
        if (! $this->discount_type || ! $this->discount_value) {
            return false;
        }

        if ($this->sale_start && $this->sale_start->isFuture()) {
            return false;
        }

        if ($this->sale_end && $this->sale_end->isPast()) {
            return false;
        }

        return true;
    }

    public function getDiscountPercentAttribute(): int
    {
        if (! $this->has_discount) {
            return 0;
        }

        return (int) round((($this->price - $this->final_price) / $this->price) * 100);
    }

    public function getFinalPriceAttribute($value): int
    {
        return (int) ($value ?? $this->price);
    }

    public function getHasDiscountAttribute(): bool
    {
        return $this->is_sale_active
            && $this->price > 0
            && $this->final_price > 0
            && $this->final_price < $this->price;
    }

    public function cardDiscountPercent(): int
    {
        if (! $this->has_discount || $this->price <= 0) {
            return 0;
        }

        if ($this->discount_type === 'percentage') {
            return (int) min(100, max(0, (int) round((float) $this->discount_value)));
        }

        if ($this->discount_type === 'fixed') {
            return (int) min(100, max(0, (int) ceil(((float) $this->discount_value / $this->price) * 100)));
        }

        return (int) min(100, max(0, (int) ceil((($this->price - $this->final_price) / $this->price) * 100)));
    }

    public static function effectiveUnitPriceSql(): string
    {
        return <<<'SQL'
CASE
    WHEN prices.final_price IS NOT NULL
         AND prices.final_price > 0
         AND prices.final_price < prices.price
         AND prices.discount_type IS NOT NULL
         AND IFNULL(prices.discount_value, 0) > 0
         AND (prices.sale_start IS NULL OR prices.sale_start <= NOW())
         AND (prices.sale_end IS NULL OR prices.sale_end >= NOW())
    THEN prices.final_price
    ELSE prices.price
END
SQL;
    }

    public static function discountPercentSql(): string
    {
        return <<<'SQL'
CASE
    WHEN prices.final_price IS NOT NULL
         AND prices.price > 0
         AND prices.final_price > 0
         AND prices.final_price < prices.price
         AND prices.discount_type IS NOT NULL
         AND IFNULL(prices.discount_value, 0) > 0
         AND (prices.sale_start IS NULL OR prices.sale_start <= NOW())
         AND (prices.sale_end IS NULL OR prices.sale_end >= NOW())
    THEN ((prices.price - prices.final_price) / prices.price) * 100
    ELSE 0
END
SQL;
    }

    public static function constrainCardPriceQuery(Builder $query): void
    {
        $query
            ->join('product_variants', 'prices.variant_id', '=', 'product_variants.id')
            ->join('inventories', 'product_variants.id', '=', 'inventories.variant_id')
            ->whereColumn('product_variants.product_id', 'products.id')
            ->where('product_variants.status', 1)
            ->where('product_variants.display_mode', VariantDisplayMode::NORMAL->value)
            ->where(function (Builder $priceQuery): void {
                $priceQuery->whereNull('prices.starts_at')
                    ->orWhere('prices.starts_at', '<=', now());
            })
            ->where(function (Builder $priceQuery): void {
                $priceQuery->whereNull('prices.ends_at')
                    ->orWhere('prices.ends_at', '>=', now());
            })
            ->whereRaw('(inventories.stock - inventories.reserved) > 0');
    }

    public static function cardAggregateQuery(string $aggregate = 'MIN', ?string $expression = null): Builder
    {
        $query = static::query();
        static::constrainCardPriceQuery($query);

        return $query->selectRaw(
            $aggregate.'('.($expression ?? static::effectiveUnitPriceSql()).')',
        );
    }

    public static function applyMinEffectivePriceSubquery(Builder $query): void
    {
        $query->from('prices');
        static::constrainCardPriceQuery($query);
        $query->selectRaw('MIN('.static::effectiveUnitPriceSql().')');
    }
}
