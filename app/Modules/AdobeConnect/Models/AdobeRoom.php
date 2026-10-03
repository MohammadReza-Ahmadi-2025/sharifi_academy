<?php

namespace App\Modules\AdobeConnect\Models;

use App\Modules\AdobeConnect\Enums\AdobeRoomAccessType;
use App\Modules\AdobeConnect\Enums\AdobeRoomStatus;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

#[Fillable([
    'course_class_id',
    'class_session_id',
    'sco_id',
    'url_path',
    'meeting_url',
    'access_type',
    'passcode',
    'status',
    'last_synced_at',
])]
class AdobeRoom extends Model
{
    /**
     * @return list<string>
     */
    public static function accessTypes(): array
    {
        return AdobeRoomAccessType::values();
    }

    /**
     * @return list<string>
     */
    public static function statuses(): array
    {
        return AdobeRoomStatus::values();
    }

    protected function casts(): array
    {
        return [
            'access_type' => AdobeRoomAccessType::class,
            'status' => AdobeRoomStatus::class,
            'last_synced_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<CourseClass, $this>
     */
    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class);
    }

    /**
     * @return BelongsTo<ClassSession, $this>
     */
    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class);
    }

    /**
     * @return HasOneThrough<Course, $this>
     */
    public function course(): HasOneThrough
    {
        return $this->hasOneThrough(
            Course::class,
            CourseClass::class,
            'id',
            'id',
            'course_class_id',
            'course_id',
        );
    }

    public function isPerSession(): bool
    {
        return $this->class_session_id !== null;
    }

    public function getAccessTypeLabelAttribute(): string
    {
        return $this->access_type?->label() ?? '';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status?->label() ?? '';
    }

    /**
     * @param  Builder<AdobeRoom>  $query
     * @return Builder<AdobeRoom>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', AdobeRoomStatus::Active);
    }
}
