<?php

namespace App\Modules\Courses\Models;

use App\Models\User;
use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\Catalog\Models\Category;
use App\Modules\Comments\Models\Comment;
use App\Modules\Courses\Enums\CourseLevel;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Files\Models\File;
use App\Modules\Visits\Concerns\HasVisits;
use App\Support\Concerns\ResolvesSeoSlugRouteBinding;
use App\Support\SeoSlug;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

#[Fillable([
    'title',
    'slug',
    'code',
    'short_description',
    'description',
    'meta_title',
    'meta_description',
    'meta_keywords',
    'canonical',
    'level',
    'duration_minutes',
    'status',
    'is_featured',
    'display_order',
    'created_by',
    'published_at',
    'certificate_enabled',
    'certificate_title',
    'certificate_min_attendance_percent',
    'certificate_min_quiz_score',
])]
class Course extends Model
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
            'code' => $this->code,
            'short_description' => strip_tags((string) $this->short_description),
            'description' => strip_tags((string) $this->description),
            'level' => $this->level?->value,
            'status' => $this->status?->value,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status === CourseStatus::Published;
    }

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
            'certificate_enabled' => 'boolean',
            'certificate_min_attendance_percent' => 'integer',
            'certificate_min_quiz_score' => 'integer',
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
     * @return MorphMany<Comment, $this>
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * @return HasManyThrough<Enrollment, $this>
     */
    public function enrollments(): HasManyThrough
    {
        return $this->hasManyThrough(
            Enrollment::class,
            CourseClass::class,
            'course_id',
            'course_class_id',
        );
    }

    /**
     * @return HasManyThrough<ClassSession, $this>
     */
    public function classSessions(): HasManyThrough
    {
        return $this->hasManyThrough(
            ClassSession::class,
            CourseClass::class,
            'course_id',
            'course_class_id',
        );
    }

    /**
     * @return HasManyThrough<AdobeRoom, $this>
     */
    public function adobeRooms(): HasManyThrough
    {
        return $this->hasManyThrough(
            AdobeRoom::class,
            CourseClass::class,
            'course_id',
            'course_class_id',
        );
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
