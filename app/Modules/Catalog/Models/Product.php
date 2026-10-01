<?php

namespace App\Modules\Catalog\Models;

use App\Models\User;

use App\Modules\Catalog\Enums\VariantDisplayMode;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'title',
    'slug',
    'sku',
    'short_description',
    'description',
    'brand_id',
    'vendor_id',
    'model',
    'status',
    'is_visible',
    'meta_keywords',
    'meta_title',
    'meta_description',
    'canonical',
    'features',
    'sort',
    'sales_count',
    'views',
    'available_stock',
    'min_effective_price',
    'card_compare_price',
    'max_discount_percent',
    'card_discount_is_campaign',
    'card_display_mode',
])]
class Product extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'is_visible' => 'boolean',
            'meta_keywords' => 'array',
            'sort' => 'integer',
            'sales_count' => 'integer',
            'views' => 'integer',
            'available_stock' => 'integer',
            'min_effective_price' => 'integer',
            'card_compare_price' => 'integer',
            'max_discount_percent' => 'integer',
            'card_discount_is_campaign' => 'boolean',
            'card_display_mode' => VariantDisplayMode::class,
        ];
    }

    /**
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories')
            ->where('type', 'product')
            ->withTimestamps();
    }

    public function leafCategory(): ?Category
    {
        $categories = $this->categories;

        return $categories->first(function (Category $category) use ($categories) {
            return $categories->contains('id', $category->parent_id);
        }) ?? $categories->first();
    }

    /**
     * @return HasMany<ProductAttributeValue, $this>
     */
    public function productAttributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class)
            ->nonVariant()
            ->with(['attribute', 'attributeValue'])
            ->orderBy('sort');
    }

    /**
     * @return HasMany<ProductVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * @return BelongsToMany<Product, $this>
     */
    public function manualRelatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'related_products',
            'product_id',
            'related_product_id',
        )->withPivot('position')
            ->orderBy('pivot_position');
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', 1);
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $builder) use ($term): void {
            $builder->where('title', 'like', "%{$term}%")
                ->orWhere('slug', 'like', "%{$term}%")
                ->orWhereHas('variants', function (Builder $variantQuery) use ($term): void {
                    $variantQuery->where('sku', 'like', "%{$term}%");
                });
        });
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeWithCardPricing(Builder $query): Builder
    {
        return $query->with(['brand']);
    }

    public function getCheapestVariantAttribute(): ?ProductVariant
    {
        return $this->variants()
            ->where('status', true)
            ->whereHas('currentPrice')
            ->whereHas('inventories', function (Builder $inventoryQuery): void {
                $inventoryQuery->whereRaw('(stock - reserved) > 0');
            })
            ->with(['currentPrice', 'inventories'])
            ->get()
            ->sortBy(fn (ProductVariant $variant) => $variant->currentPrice?->has_discount
                ? $variant->currentPrice->final_price
                : $variant->currentPrice?->price)
            ->first();
    }

    public function getMinPriceAttribute(): ?int
    {
        return $this->attributes['min_effective_price'] ?? $this->cheapest_variant?->currentPrice?->final_price;
    }

    public function getMinDiscountPriceAttribute(): ?int
    {
        return $this->min_price;
    }

    public function getDiscountPercentAttribute(): int
    {
        if (array_key_exists('max_discount_percent', $this->attributes)) {
            return (int) ($this->attributes['max_discount_percent'] ?? 0);
        }

        return $this->cheapest_variant?->currentPrice?->discount_percent ?? 0;
    }

    /**
     * @return HasMany<ProductVariant, $this>
     */
    public function inStockVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)
            ->where('status', 1)
            ->whereHas('inventories', function (Builder $inventoryQuery): void {
                $inventoryQuery->whereRaw('(stock - reserved) > 0');
            });
    }

    /**
     * @return HasMany<ProductVariant, $this>
     */
    public function callForPriceVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)
            ->where('status', 1)
            ->where('display_mode', VariantDisplayMode::CALL_FOR_PRICE->value);
    }

    /**
     * @return HasMany<ProductVariant, $this>
     */
    public function outOfStockVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)
            ->where('status', 1)
            ->where('display_mode', VariantDisplayMode::OUT_OF_STOCK->value);
    }
}
