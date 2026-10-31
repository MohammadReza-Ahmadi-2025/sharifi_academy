<?php

namespace App\Modules\Assessments\Models;

use App\Modules\Courses\Models\Course;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'course_id',
    'title',
    'description',
    'pass_score',
    'is_published',
    'time_limit_minutes',
    'max_attempts',
])]
class Quiz extends Model
{
    protected function casts(): array
    {
        return [
            'pass_score' => 'integer',
            'is_published' => 'boolean',
            'time_limit_minutes' => 'integer',
            'max_attempts' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * @return HasMany<QuizQuestion, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('sort')->orderBy('id');
    }

    /**
     * @return HasMany<QuizAttempt, $this>
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /**
     * @param  Builder<Quiz>  $query
     * @return Builder<Quiz>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
