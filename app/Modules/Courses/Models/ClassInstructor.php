<?php

namespace App\Modules\Courses\Models;

use App\Models\User;
use App\Modules\Courses\Enums\ClassInstructorRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

#[Fillable([
    'course_class_id',
    'user_id',
    'role',
    'is_primary',
    'share_percentage',
])]
class ClassInstructor extends Model
{
    /**
     * @return list<string>
     */
    public static function roles(): array
    {
        return ClassInstructorRole::values();
    }

    /**
     * @return array<string, string>
     */
    public static function roleLabels(): array
    {
        $labels = [];

        foreach (ClassInstructorRole::cases() as $case) {
            $labels[$case->value] = $case->label();
        }

        return $labels;
    }

    public static function roleLabel(string $role): string
    {
        return ClassInstructorRole::tryFrom($role)?->label() ?? $role;
    }

    protected function casts(): array
    {
        return [
            'role' => ClassInstructorRole::class,
            'is_primary' => 'boolean',
            'share_percentage' => 'decimal:2',
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

    public function getRoleLabelAttribute(): string
    {
        return $this->role?->label() ?? '';
    }
}
