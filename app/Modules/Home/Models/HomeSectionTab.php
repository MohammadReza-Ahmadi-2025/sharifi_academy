<?php

namespace App\Modules\Home\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'section_id',
    'title',
    'content_type',
    'source_type',
    'limit',
    'sort_order',
    'is_active',
    'settings',
])]
class HomeSectionTab extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    /**
     * @return BelongsTo<HomeSection, $this>
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(HomeSection::class, 'section_id');
    }

    /**
     * @return HasMany<HomeSectionItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(HomeSectionItem::class, 'tab_id')
            ->orderBy('sort_order');
    }
}
