<?php

namespace App\Modules\AdobeConnect\Models;

use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\CourseClass;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

#[Fillable([
    'class_session_id',
    'sco_id',
    'name',
    'playback_url',
    'duration_seconds',
    'size_bytes',
    'is_public',
    'is_passcode_protected',
    'passcode',
])]
class AdobeRecording extends Model
{
    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
            'size_bytes' => 'integer',
            'is_public' => 'boolean',
            'is_passcode_protected' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<ClassSession, $this>
     */
    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class);
    }

    /**
     * @return HasOneThrough<CourseClass, $this>
     */
    public function courseClass(): HasOneThrough
    {
        return $this->hasOneThrough(
            CourseClass::class,
            ClassSession::class,
            'id',
            'id',
            'class_session_id',
            'course_class_id',
        );
    }

    /**
     * @param  Builder<AdobeRecording>  $query
     * @return Builder<AdobeRecording>
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }
}
