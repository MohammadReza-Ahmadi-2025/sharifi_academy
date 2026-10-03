<?php

namespace App\Modules\Home\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'title',
    'status',
    'sort',
    'settings',
])]
class HomeSection extends Model
{
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'settings' => 'array',
        ];
    }

    /**
     * @return HasMany<HomeSectionTab, $this>
     */
    public function tabs(): HasMany
    {
        return $this->hasMany(HomeSectionTab::class, 'section_id')
            ->orderBy('sort_order');
    }
}
