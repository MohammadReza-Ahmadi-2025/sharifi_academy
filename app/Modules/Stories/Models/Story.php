<?php

namespace App\Modules\Stories\Models;

use App\Models\User;
use App\Modules\Files\Models\File;
use App\Modules\Stories\Enums\StoryStatus;
use App\Modules\Stories\Enums\StoryType;
use App\Modules\Stories\Enums\StoryVisibility;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'file_id',
    'type',
    'content',
    'background_color',
    'status',
    'visibility',
    'published_at',
    'expires_at',
])]
class Story extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'type' => StoryType::class,
            'status' => StoryStatus::class,
            'visibility' => StoryVisibility::class,
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<File, $this>
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * @return MorphMany<File, $this>
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    /**
     * @return HasMany<StoryView, $this>
     */
    public function views(): HasMany
    {
        return $this->hasMany(StoryView::class);
    }

    /**
     * @param  Builder<Story>  $query
     * @return Builder<Story>
     */
    public function scopePublishedActive(Builder $query): Builder
    {
        return $query
            ->where('status', StoryStatus::Published)
            ->whereNotNull('published_at')
            ->where(function (Builder $builder): void {
                $builder->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * @param  Builder<Story>  $query
     * @return Builder<Story>
     */
    public function scopeVisibleTo(Builder $query, ?User $viewer): Builder
    {
        return $query->where(function (Builder $builder) use ($viewer): void {
            $builder->where('visibility', StoryVisibility::Public);

            if ($viewer !== null) {
                $builder->orWhere('visibility', StoryVisibility::Authenticated);
            }
        });
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
