<?php

namespace App\Modules\Blog\Models;

use App\Models\User;
use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Catalog\Models\Category;
use App\Modules\Comments\Models\Comment;
use App\Modules\Files\Models\File;
use App\Modules\Files\Services\MorphFileSyncService;
use App\Modules\Visits\Concerns\HasVisits;
use App\Support\Concerns\ResolvesSeoSlugRouteBinding;
use App\Support\SeoSlug;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

#[Fillable([
    'author_id',
    'title',
    'slug',
    'excerpt',
    'content',
    'published_at',
    'status',
    'meta_title',
    'meta_description',
    'meta_keywords',
    'canonical',
    'views',
])]
class Post extends Model
{
    use HasVisits;
    use ResolvesSeoSlugRouteBinding;
    use Searchable;
    use SoftDeletes;

    public function publicSlug(): string
    {
        return SeoSlug::forUrl((string) $this->slug);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => strip_tags((string) $this->excerpt),
            'content' => strip_tags((string) $this->content),
            'status' => $this->status?->value,
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status === PostStatus::Published
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'meta_keywords' => 'array',
            'views' => 'integer',
            'status' => PostStatus::class,
        ];
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'post_categories')
            ->where('type', Category::TYPE_BLOG)
            ->withTimestamps();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return MorphOne<File, $this>
     */
    public function featuredImage(): MorphOne
    {
        return $this->morphOne(File::class, 'fileable')
            ->where('type', MorphFileSyncService::TYPE_FEATURED);
    }

    /**
     * @return MorphMany<File, $this>
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable')->orderBy('sort');
    }

    /**
     * @return MorphMany<Comment, $this>
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', PostStatus::Published->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
