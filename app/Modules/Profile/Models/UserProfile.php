<?php

namespace App\Modules\Profile\Models;

use App\Models\User;
use App\Modules\Locations\Models\ProvinceCity;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'headline',
    'bio',
    'birth_date',
    'national_code',
    'province_city_id',
    'is_public',
    'is_verified',
])]
class UserProfile extends Model
{
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'is_public' => 'boolean',
            'is_verified' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function provinceCity(): BelongsTo
    {
        return $this->belongsTo(ProvinceCity::class);
    }
}
