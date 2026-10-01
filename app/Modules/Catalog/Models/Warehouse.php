<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

#[Fillable([
    'name',
    'code',
    'address',
    'phone',
    'is_default',
    'is_active',
])]
class Warehouse extends Model
{
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('warehouse.default_id'));
        static::deleted(fn () => Cache::forget('warehouse.default_id'));

        static::saving(function (Warehouse $warehouse): void {
            if ($warehouse->is_default) {
                static::query()
                    ->where('id', '!=', $warehouse->id ?? 0)
                    ->update(['is_default' => false]);
            }
        });
    }

    /**
     * @return HasMany<Inventory, $this>
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public static function defaultId(): int
    {
        return Cache::rememberForever('warehouse.default_id', function () {
            $id = static::query()->where('is_default', true)->value('id');

            if ($id) {
                return (int) $id;
            }

            return (int) static::query()->where('is_active', true)->orderBy('id')->value('id');
        });
    }

    public static function default(): ?self
    {
        return static::query()->find(static::defaultId());
    }
}
