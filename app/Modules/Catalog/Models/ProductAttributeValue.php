<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'product_id',
    'attribute_id',
    'attribute_value_id',
    'value',
    'is_highlight',
    'sort',
])]
class ProductAttributeValue extends Model
{
    protected function casts(): array
    {
        return [
            'is_highlight' => 'boolean',
            'sort' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Attribute, $this>
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * @return BelongsTo<AttributeValue, $this>
     */
    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(AttributeValue::class);
    }

    /**
     * @param  Builder<ProductAttributeValue>  $query
     * @return Builder<ProductAttributeValue>
     */
    public function scopeNonVariant(Builder $query): Builder
    {
        return $query->whereHas('attribute', function (Builder $attributeQuery): void {
            $attributeQuery->where('is_variant', false);
        });
    }
}
