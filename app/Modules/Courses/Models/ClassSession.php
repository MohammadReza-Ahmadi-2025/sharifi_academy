<?php

namespace App\Modules\Courses\Models;

use App\Models\User;
use App\Modules\AdobeConnect\Models\AdobeRecording;
use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\Courses\Enums\ClassSessionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'course_class_id',
    'session_number',
    'title',
    'description',
    'video_url',
    'status',
    'scheduled_start_at',
    'scheduled_end_at',
    'actual_start_at',
    'actual_end_at',
    'timezone',
    'cancelled_at',
    'cancellation_reason',
    'created_by',
])]
class ClassSession extends Model
{
    use SoftDeletes;

    /**
     * @return list<string>
     */
    public static function statuses(): array
    {
        return ClassSessionStatus::values();
    }

    /**
     * @return array<string, string>
     */
    public static function statusLabels(): array
    {
        $labels = [];

        foreach (ClassSessionStatus::cases() as $case) {
            $labels[$case->value] = $case->label();
        }

        return $labels;
    }

    protected function casts(): array
    {
        return [
            'session_number' => 'integer',
            'status' => ClassSessionStatus::class,
            'scheduled_start_at' => 'datetime',
            'scheduled_end_at' => 'datetime',
            'actual_start_at' => 'datetime',
            'actual_end_at' => 'datetime',
            'cancelled_at' => 'datetime',
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
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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

    /**
     * ثبت‌نام‌ها در سطح کلاس هستند؛ همهٔ دانشجویان همان کلاس به این جلسه مربوط می‌شوند.
     *
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'course_class_id', 'course_class_id');
    }

    /**
     * @return HasMany<AdobeRoom, $this>
     */
    public function adobeRooms(): HasMany
    {
        return $this->hasMany(AdobeRoom::class);
    }

    /**
     * @return HasMany<AdobeRecording, $this>
     */
    public function adobeRecordings(): HasMany
    {
        return $this->hasMany(AdobeRecording::class);
    }

    /**
     * @return HasMany<SessionAttendance, $this>
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(SessionAttendance::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status?->label() ?? '';
    }

    /**
     * @param  Builder<ClassSession>  $query
     * @return Builder<ClassSession>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('session_number');
    }

    /**
     * @param  Builder<ClassSession>  $query
     * @return Builder<ClassSession>
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query
            ->where('status', ClassSessionStatus::Scheduled)
            ->where('scheduled_start_at', '>=', now());
    }
}
