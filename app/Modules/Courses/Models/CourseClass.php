<?php

namespace App\Modules\Courses\Models;

use App\Models\User;
use App\Modules\AdobeConnect\Models\AdobeRecording;
use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\Courses\Enums\CourseClassDeliveryType;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\EnrollmentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'course_id',
    'name',
    'code',
    'status',
    'delivery_type',
    'capacity',
    'price',
    'registration_starts_at',
    'registration_ends_at',
    'starts_at',
    'ends_at',
    'location',
    'description',
    'is_public',
    'created_by',
])]
class CourseClass extends Model
{
    use SoftDeletes;

    /**
     * @return list<string>
     */
    public static function statuses(): array
    {
        return CourseClassStatus::values();
    }

    /**
     * @return list<string>
     */
    public static function deliveryTypes(): array
    {
        return CourseClassDeliveryType::values();
    }

    /**
     * @return array<string, string>
     */
    public static function deliveryTypeLabels(): array
    {
        $labels = [];

        foreach (CourseClassDeliveryType::cases() as $case) {
            $labels[$case->value] = $case->label();
        }

        return $labels;
    }

    public static function deliveryTypeLabel(string $deliveryType): string
    {
        return CourseClassDeliveryType::tryFrom($deliveryType)?->label() ?? $deliveryType;
    }

    public function getDeliveryTypeLabelAttribute(): string
    {
        return $this->delivery_type?->label() ?? '';
    }

    protected function casts(): array
    {
        return [
            'status' => CourseClassStatus::class,
            'delivery_type' => CourseClassDeliveryType::class,
            'capacity' => 'integer',
            'price' => 'integer',
            'registration_starts_at' => 'datetime',
            'registration_ends_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_public' => 'boolean',
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
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<ClassInstructor, $this>
     */
    public function classInstructors(): HasMany
    {
        return $this->hasMany(ClassInstructor::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_instructors')
            ->withPivot(['role', 'is_primary', 'share_percentage'])
            ->withTimestamps();
    }

    /**
     * @return HasOne<ClassInstructor, $this>
     */
    public function primaryInstructorAssignment(): HasOne
    {
        return $this->hasOne(ClassInstructor::class)->where('is_primary', true);
    }

    /**
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function enrolledStudents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments')
            ->withPivot([
                'status',
                'confirmed_at',
                'cancelled_at',
                'reserved_until',
                'price_amount',
                'admin_note',
            ])
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function students(): BelongsToMany
    {
        return $this->enrolledStudents();
    }

    /**
     * @return HasMany<Enrollment, $this>
     */
    public function confirmedEnrollments(): HasMany
    {
        return $this->enrollments()->where('status', EnrollmentStatus::Confirmed);
    }

    /**
     * @return HasMany<ClassSession, $this>
     */
    public function classSessions(): HasMany
    {
        return $this->hasMany(ClassSession::class)->ordered();
    }

    /**
     * @return HasMany<AdobeRoom, $this>
     */
    public function adobeRooms(): HasMany
    {
        return $this->hasMany(AdobeRoom::class);
    }

    /**
     * اتاق‌های سطح کلاس (سیاست Per-Class).
     *
     * @return HasMany<AdobeRoom, $this>
     */
    public function classLevelAdobeRooms(): HasMany
    {
        return $this->adobeRooms()->whereNull('class_session_id');
    }

    /**
     * اتاق‌های متصل به جلسات مشخص (سیاست Per-Session).
     *
     * @return HasMany<AdobeRoom, $this>
     */
    public function sessionAdobeRooms(): HasMany
    {
        return $this->adobeRooms()->whereNotNull('class_session_id');
    }

    /**
     * @return HasManyThrough<AdobeRecording, $this>
     */
    public function adobeRecordings(): HasManyThrough
    {
        return $this->hasManyThrough(
            AdobeRecording::class,
            ClassSession::class,
            'course_class_id',
            'class_session_id',
        );
    }

    /**
     * @param  Builder<CourseClass>  $query
     * @return Builder<CourseClass>
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * @param  Builder<CourseClass>  $query
     * @return Builder<CourseClass>
     */
    public function scopeOpenForRegistration(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [CourseClassStatus::Upcoming, CourseClassStatus::Ongoing])
            ->where(function (Builder $registrationQuery): void {
                $registrationQuery
                    ->whereNull('registration_starts_at')
                    ->orWhere('registration_starts_at', '<=', now());
            })
            ->where(function (Builder $registrationQuery): void {
                $registrationQuery
                    ->whereNull('registration_ends_at')
                    ->orWhere('registration_ends_at', '>=', now());
            });
    }
}
