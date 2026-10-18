<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Visits\Concerns\HasVisits;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

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
    use Searchable;
    use SoftDeletes;

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => strip_tags((string) $this->description),
            'status' => $this->status,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return (bool) $this->status;
    }

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
