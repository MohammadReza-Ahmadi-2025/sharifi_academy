<?php

namespace App\Modules\Locations\Models;

use App\Models\User;
use App\Modules\Profile\Models\UserProfile;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'vista_id',
    'user_id',
    'parent_id',
    'title',
    'sort',
])]
class ProvinceCity extends Model
{
    use SoftDeletes;

    protected $table = 'province_cities';

    protected function casts(): array
    {
        return [
            'vista_id' => 'integer',
            'user_id' => 'integer',
            'parent_id' => 'integer',
            'sort' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<ProvinceCity, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort')->orderBy('title');
    }

    public function isProvince(): bool
    {
        return $this->parent_id === null;
    }

    public function isCity(): bool
    {
        return $this->parent_id !== null;
    }

    /**
     * @return HasMany<UserProfile, $this>
     */
    public function userProfiles(): HasMany
    {
        return $this->hasMany(UserProfile::class);
    }
}
