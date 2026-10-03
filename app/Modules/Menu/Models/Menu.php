<?php

namespace App\Modules\Menu\Models;

use App\Modules\Menu\Enums\MenuLocation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'title',
    'url',
    'icon',
    'location',
    'sort',
    'status',
])]
class Menu extends Model
{
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'sort' => 'integer',
            'location' => MenuLocation::class,
        ];
    }

    /**
     * @return HasMany<MenuItem, $this>
     */
    public function allItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort');
    }

    /**
     * @return HasMany<MenuItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)
            ->where('status', true)
            ->orderBy('sort');
    }
}
