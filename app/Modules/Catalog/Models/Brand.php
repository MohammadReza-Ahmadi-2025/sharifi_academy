<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Visits\Concerns\HasVisits;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'title',
    'slug',
    'description',
    'website',
    'status',
    'meta_title',
    'meta_keywords',
    'meta_description',
    'canonical',
    'sort',
    'views',
])]
class Brand extends Model
{
    use HasVisits;
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'meta_keywords' => 'array',
            'status' => 'boolean',
            'sort' => 'integer',
            'views' => 'integer',
        ];
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
