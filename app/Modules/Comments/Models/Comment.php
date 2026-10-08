<?php

namespace App\Modules\Comments\Models;

use App\Models\User;
use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Product;
use App\Modules\Courses\Models\Course;
use App\Modules\Comments\Enums\CommenterType;
use App\Modules\Comments\Enums\CommentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'commentable_type',
    'commentable_id',
    'user_id',
    'commenter_type',
    'display_name',
    'parent_id',
    'body',
    'rating',
    'status',
    'is_anonymous',
    'moderated_by',
    'moderated_at',
])]
class Comment extends Model
{
    protected function casts(): array
    {
        return [
            'status' => CommentStatus::class,
            'commenter_type' => CommenterType::class,
            'is_anonymous' => 'boolean',
            'moderated_at' => 'datetime',
            'rating' => 'integer',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    /**
     * @return BelongsTo<Comment, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('created_at');
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function childrenRecursive(): HasMany
    {
        return $this->children()->with(['childrenRecursive', 'user', 'moderator']);
    }

    /**
     * @param  Builder<Comment>  $query
     * @return Builder<Comment>
     */
    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function getAuthorNameAttribute(): string
    {
        if (filled($this->display_name)) {
            return (string) $this->display_name;
        }

        if ($this->is_anonymous) {
            return 'کاربر';
        }

        $user = $this->user;

        if ($user?->name) {
            return trim($user->name.' '.($user->family ?? ''));
        }

        return 'کاربر';
    }

    public function commentableTypeLabel(): string
    {
        return match ($this->commentable_type) {
            Product::class => 'محصول',
            Post::class => 'مقاله',
            Course::class => 'دوره',
            default => class_basename($this->commentable_type) ?: '—',
        };
    }

    public function commentableTitle(): ?string
    {
        $commentable = $this->commentable;

        return $commentable?->title ?? null;
    }
}
