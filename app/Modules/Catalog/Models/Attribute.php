<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
    'type',
    'is_filterable',
    'is_visible_on_product',
    'is_variant',
    'is_active',
    'sort',
])]
class Attribute extends Model
{
    protected function casts(): array
    {
        return [
            'is_filterable' => 'boolean',
            'is_visible_on_product' => 'boolean',
            'is_variant' => 'boolean',
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    /**
     * @return HasMany<AttributeValue, $this>
     */
    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }
}
