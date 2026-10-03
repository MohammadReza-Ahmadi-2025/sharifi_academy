<?php

namespace App\Modules\Courses\Models;

use App\Models\User;
use App\Modules\Courses\Enums\EnrollmentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

#[Fillable([
    'course_class_id',
    'user_id',
    'status',
    'confirmed_at',
    'cancelled_at',
    'reserved_until',
    'price_amount',
    'admin_note',
])]
class Enrollment extends Model
{
    /**
     * @return list<string>
     */
    public static function statuses(): array
    {
        return EnrollmentStatus::values();
    }

    /**
     * @return array<string, string>
     */
    public static function statusLabels(): array
    {
        $labels = [];

        foreach (EnrollmentStatus::cases() as $case) {
            $labels[$case->value] = $case->label();
        }

        return $labels;
    }

    protected function casts(): array
    {
        return [
            'status' => EnrollmentStatus::class,
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'reserved_until' => 'datetime',
            'price_amount' => 'integer',
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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function isPending(): bool
    {
        return $this->status === EnrollmentStatus::Pending;
    }

    public function isConfirmed(): bool
    {
        return $this->status === EnrollmentStatus::Confirmed;
    }

    public function isCancelled(): bool
    {
        return $this->status === EnrollmentStatus::Cancelled;
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status?->label() ?? '';
    }

    /**
     * @param  Builder<Enrollment>  $query
     * @return Builder<Enrollment>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', EnrollmentStatus::Pending);
    }

    /**
     * @param  Builder<Enrollment>  $query
     * @return Builder<Enrollment>
     */
    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', EnrollmentStatus::Confirmed);
    }

    /**
     * @param  Builder<Enrollment>  $query
     * @return Builder<Enrollment>
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', EnrollmentStatus::Cancelled);
    }

    /**
     * @param  Builder<Enrollment>  $query
     * @return Builder<Enrollment>
     */
    public function scopeReserved(Builder $query): Builder
    {
        return $query
            ->pending()
            ->whereNotNull('reserved_until')
            ->where('reserved_until', '>', now());
    }
}
