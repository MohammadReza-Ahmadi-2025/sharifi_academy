<?php

namespace App\Modules\Courses\Models;

use App\Models\User;
use App\Modules\Courses\Enums\SessionAttendanceRole;
use App\Modules\Courses\Enums\SessionAttendanceSource;
use App\Modules\Courses\Enums\SessionAttendanceStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'class_session_id',
    'user_id',
    'status',
    'role',
    'first_joined_at',
    'last_left_at',
    'duration_minutes',
    'source',
    'note',
])]
class SessionAttendance extends Model
{
    /**
     * @return list<string>
     */
    public static function statuses(): array
    {
        return SessionAttendanceStatus::values();
    }

    protected function casts(): array
    {
        return [
            'status' => SessionAttendanceStatus::class,
            'role' => SessionAttendanceRole::class,
            'source' => SessionAttendanceSource::class,
            'first_joined_at' => 'datetime',
            'last_left_at' => 'datetime',
            'duration_minutes' => 'integer',
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
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status?->label() ?? '';
    }

    public function getRoleLabelAttribute(): string
    {
        return $this->role?->label() ?? '';
    }

    public function getSourceLabelAttribute(): string
    {
        return $this->source?->label() ?? '';
    }

    /**
     * @param  Builder<SessionAttendance>  $query
     * @return Builder<SessionAttendance>
     */
    public function scopeForSession(Builder $query, int $classSessionId): Builder
    {
        return $query->where('class_session_id', $classSessionId);
    }

    /**
     * @param  Builder<SessionAttendance>  $query
     * @return Builder<SessionAttendance>
     */
    public function scopePresent(Builder $query): Builder
    {
        return $query->where('status', SessionAttendanceStatus::Present);
    }
}
