<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Cart\Models\CartItem;
use App\Modules\Catalog\Enums\VariantDisplayMode;
use App\Modules\Catalog\Services\InventoryService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

#[Fillable([
    'product_id',
    'barcode',
    'sku',
    'title',
    'combination_hash',
    'status',
    'sort',
    'sales_count',
    'display_mode',
])]
class ProductVariant extends Model
{
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'display_mode' => VariantDisplayMode::class,
            'sort' => 'integer',
            'sales_count' => 'integer',
        ];
    }

    public static function combinationHashFor(iterable $attributeValueIds): string
    {
        return sha1(
            collect($attributeValueIds)
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->implode('-'),
        );
    }

    public static function manualCombinationHash(int $productId, string $seed): string
    {
        return sha1('manual:'.$productId.':'.$seed);
    }

    /**
     * @param  iterable<int|numeric-string>  $attributeValueIds
     */
    public function syncAttributeValuesWithHash(iterable $attributeValueIds): void
    {
        $attributeValueIds = collect($attributeValueIds)->map(fn ($id) => (int) $id)->filter()->values()->all();
        $hash = static::combinationHashFor($attributeValueIds);

        $duplicate = static::query()
            ->where('product_id', $this->product_id)
            ->where('combination_hash', $hash)
            ->whereKeyNot($this->id)
            ->exists();

        if ($duplicate) {
            throw new \DomainException('ترکیب ویژگی‌های این تنوع تکراری است و قبلاً برای این کالا ثبت شده.');
        }

        $this->attributeValues()->sync($attributeValueIds);
        $this->forceFill(['combination_hash' => $hash])->saveQuietly();
    }

    public function isCallForPrice(): bool
    {
        return $this->display_mode === VariantDisplayMode::CALL_FOR_PRICE;
    }

    public function isNormal(): bool
    {
        return $this->display_mode === VariantDisplayMode::NORMAL;
    }

    public function isOutOfStock(): bool
    {
        return $this->display_mode === VariantDisplayMode::OUT_OF_STOCK;
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return HasMany<Price, $this>
     */
    public function prices(): HasMany
    {
        return $this->hasMany(Price::class, 'variant_id');
    }

    /**
     * @return HasOne<Price, $this>
     */
    public function currentPrice(): HasOne
    {
        return $this->hasOne(Price::class, 'variant_id')->active();
    }

    /**
     * @return HasMany<Inventory, $this>
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class, 'variant_id');
    }

    /**
     * @return HasOne<Inventory, $this>
     */
    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class, 'variant_id')
            ->where('warehouse_id', Warehouse::defaultId());
    }

    public function getTotalAvailableAttribute(): int
    {
        if ($this->relationLoaded('inventories')) {
            return (int) $this->inventories->sum(fn (Inventory $inventory) => $inventory->available);
        }

        return app(InventoryService::class)->availableQuantity($this->id);
    }

    /**
     * @return HasMany<InventoryLog, $this>
     */
    public function inventoryLogs(): HasMany
    {
        return $this->hasMany(InventoryLog::class, 'variant_id');
    }

    /**
     * @return HasMany<CartItem, $this>
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'variant_id');
    }

    /**
     * @return BelongsToMany<AttributeValue, $this>
     */
    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'product_variant_values',
            'product_variant_id',
            'attribute_value_id',
        );
    }

    /**
     * @return Collection<string, string>
     */
    public function getAttributesMapAttribute(): Collection
    {
        return $this->attributeValues
            ->loadMissing('attribute')
            ->mapWithKeys(function (AttributeValue $value) {
                return [
                    $value->attribute?->name => $value->value,
                ];
            })
            ->filter();
    }

    public function getLabelAttribute(): string
    {
        if ($this->title) {
            return $this->title;
        }

        return $this->attributeValues
            ->loadMissing('attribute')
            ->map(fn (AttributeValue $value) => $value->value)
            ->join(' / ');
    }

    public function getAttributesTextAttribute(): string
    {
        return $this->attributes_map
            ->map(fn ($value, $key) => "$key: $value")
            ->join(' | ');
    }

    /**
     * @param  Builder<ProductVariant>  $query
     * @return Builder<ProductVariant>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }
}
