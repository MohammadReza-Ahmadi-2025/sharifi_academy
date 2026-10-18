<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Blog\Models\Post;
use App\Modules\Courses\Models\Course;
use App\Modules\Files\Models\File;
use App\Modules\Files\Services\MorphFileSyncService;
use App\Modules\Profile\Models\Skill;
use App\Modules\Visits\Concerns\HasVisits;
use App\Support\Concerns\ResolvesSeoSlugRouteBinding;
use App\Support\SeoSlug;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

#[Fillable([
    'parent_id',
    'type',
    'title',
    'slug',
    'description',
    'meta_title',
    'meta_description',
    'meta_keywords',
    'canonical',
    'show_in_menu',
    'sort',
    'status',
    'views',
])]
class Category extends Model
{
    use HasVisits;
    use ResolvesSeoSlugRouteBinding;
    use Searchable;
    use SoftDeletes;

    public function publicSlug(): string
    {
        return SeoSlug::forUrl((string) $this->slug);
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
            'type' => $this->type,
            'description' => strip_tags((string) $this->description),
            'status' => $this->status,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return (bool) $this->status
            && in_array($this->type, [self::TYPE_PRODUCT, self::TYPE_COURSE, self::TYPE_BLOG], true);
    }

    public const TYPE_PRODUCT = 'product';

    public const TYPE_BLOG = 'blog';

    public const TYPE_FAQ = 'faq';

    public const TYPE_SERVICE = 'service';

    public const TYPE_COURSE = 'course';

    protected function casts(): array
    {
        return [
            'meta_keywords' => 'array',
            'show_in_menu' => 'boolean',
            'status' => 'boolean',
            'sort' => 'integer',
            'parent_id' => 'integer',
            'type' => 'string',
            'views' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort');
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function activeChildren(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->where('status', 1)
            ->orderBy('sort');
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    /**
     * @param  Builder<Category>  $query
     * @return Builder<Category>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    /**
     * @param  Builder<Category>  $query
     * @return Builder<Category>
     */
    public function scopeProduct(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_PRODUCT);
    }

    /**
     * @param  Builder<Category>  $query
     * @return Builder<Category>
     */
    public function scopeBlog(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_BLOG);
    }

    /**
     * @param  Builder<Category>  $query
     * @return Builder<Category>
     */
    public function scopeService(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_SERVICE);
    }

    /**
     * @param  Builder<Category>  $query
     * @return Builder<Category>
     */
    public function scopeFaq(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_FAQ);
    }

    /**
     * @param  Builder<Category>  $query
     * @return Builder<Category>
     */
    public function scopeCourse(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_COURSE);
    }

    public function root(): self
    {
        $category = $this;

        while ($category->parent) {
            $category = $category->parent;
        }

        return $category;
    }

    /**
     * @return BelongsToMany<Product, $this>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_categories')
            ->withTimestamps();
    }

    /**
     * @return HasMany<Skill, $this>
     */
    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class);
    }

    /**
     * @return BelongsToMany<Course, $this>
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_categories')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Post, $this>
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_categories')
            ->withTimestamps();
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
    public function image(): MorphOne
    {
        return $this->morphOne(File::class, 'fileable')
            ->where('type', MorphFileSyncService::TYPE_IMAGE);
    }
}
