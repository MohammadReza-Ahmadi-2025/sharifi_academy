<?php

namespace App\Modules\Pages\Models;

use App\Support\Concerns\ResolvesSeoSlugRouteBinding;
use App\Support\SeoSlug;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'slug',
    'content',
    'is_active',
    'meta_title',
    'meta_description',
    'meta_keywords',
    'canonical',
])]
class Page extends Model
{
    use ResolvesSeoSlugRouteBinding;

    public function publicSlug(): string
    {
        return SeoSlug::forUrl((string) $this->slug);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'meta_keywords' => 'array',
        ];
    }
}
