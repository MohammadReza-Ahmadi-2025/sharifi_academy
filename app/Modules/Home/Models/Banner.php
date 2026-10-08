<?php

namespace App\Modules\Home\Models;

use App\Modules\Files\Models\File;
use App\Modules\Files\Services\MorphFileSyncService;
use App\Modules\Home\Enums\BannerLocation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

#[Fillable([
    'title',
    'subtitle',
    'button_text',
    'button_link',
    'starts_at',
    'ends_at',
    'location',
])]
class Banner extends Model
{
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'location' => BannerLocation::class,
        ];
    }

    /**
     * @param  Builder<Banner>  $query
     * @return Builder<Banner>
     */
    public function scopeAvailable(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where(function (Builder $builder) use ($now) {
                $builder->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $builder) use ($now) {
                $builder->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $now);
            });
    }

    /**
     * @return MorphMany<File, $this>
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable')->orderBy('sort');
    }

    /**
     * @return MorphOne<File, $this>
     */
    public function file(): MorphOne
    {
        return $this->morphOne(File::class, 'fileable')
            ->where('type', MorphFileSyncService::TYPE_IMAGE);
    }
}
