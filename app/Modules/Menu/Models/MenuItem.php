<?php

namespace App\Modules\Menu\Models;

use App\Modules\Menu\Enums\MenuItemType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'menu_id',
    'title',
    'type',
    'url',
    'reference_id',
    'sort',
    'status',
    'target_blank',
])]
class MenuItem extends Model
{
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'sort' => 'integer',
            'target_blank' => 'boolean',
            'type' => MenuItemType::class,
        ];
    }

    /**
     * @return BelongsTo<Menu, $this>
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
