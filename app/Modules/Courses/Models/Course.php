<?php

namespace App\Modules\Courses\Models;

use App\Models\User;
use App\Modules\Catalog\Models\Category;
use App\Modules\Files\Models\File;

use App\Modules\Courses\Enums\CourseLevel;
use App\Modules\Courses\Enums\CourseStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'title',
    'slug',
    'code',
    'short_description',
    'description',
    'level',
    'duration_minutes',
    'status',
    'is_featured',
    'display_order',
    'created_by',
    'published_at',
])]
class Course extends Model
{
    use SoftDeletes;

    /**
     * @return list<string>
     */
    public static function statuses(): array
    {
        return CourseStatus::values();
    }

    /**
     * @return list<string>
     */
    public static function levels(): array
    {
        return CourseLevel::values();
    }

    protected function casts(): array
    {
        return [
            'status' => CourseStatus::class,
            'level' => CourseLevel::class,
            'duration_minutes' => 'integer',
            'is_featured' => 'boolean',
            'display_order' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'course_categories')
            ->where('categories.type', Category::TYPE_COURSE)
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
    public function mainImage(): MorphOne
    {
        return $this->morphOne(File::class, 'fileable')
            ->where('type', 'gallery')
            ->ofMany('sort', 'min');
    }

    /**
     * @return MorphMany<File, $this>
     */
    public function gallery(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable')
            ->where('type', 'gallery')
            ->orderBy('sort');
    }

    /**
     * @return HasMany<CourseClass, $this>
     */
    public function courseClasses(): HasMany
    {
        return $this->hasMany(CourseClass::class);
    }

    /**
     * @param  Builder<Course>  $query
     * @return Builder<Course>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', CourseStatus::Published);
    }

    /**
     * @param  Builder<Course>  $query
     * @return Builder<Course>
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * @param  Builder<Course>  $query
     * @return Builder<Course>
     */
    public function scopeOrderedForDisplay(Builder $query): Builder
    {
        return $query->orderBy('display_order')->orderByDesc('published_at');
    }
}
