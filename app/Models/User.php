<?php

namespace App\Models;

use App\Models\Concerns\HasMediaFiles;
use App\Modules\Courses\Models\ClassInstructor;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Profile\Models\Resume;
use App\Modules\Profile\Models\Skill;
use App\Modules\Profile\Models\UserProfile;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name',
    'family',
    'mobile',
    'email',
    'gender',
    'status',
    'password',
    'last_login_at',
    'last_login_ip',
    'email_verified_at',
    'mobile_verified_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasMediaFiles, HasRoles, Notifiable, SoftDeletes;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif',
            ]);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'mobile_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'integer',
        ];
    }

    /**
     * @return HasOne<UserProfile, $this>
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * @return HasMany<Resume, $this>
     */
    public function resumes(): HasMany
    {
        return $this->hasMany(Resume::class)
            ->orderBy('display_order')
            ->orderByDesc('start_date');
    }

    /**
     * @return BelongsToMany<Skill, $this>
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class)
            ->withPivot('level')
            ->withTimestamps();
    }

    /**
     * @return HasMany<Course, $this>
     */
    public function createdCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'created_by');
    }

    /**
     * @return HasMany<CourseClass, $this>
     */
    public function createdCourseClasses(): HasMany
    {
        return $this->hasMany(CourseClass::class, 'created_by');
    }

    /**
     * @return HasMany<ClassInstructor, $this>
     */
    public function classInstructorAssignments(): HasMany
    {
        return $this->hasMany(ClassInstructor::class);
    }

    /**
     * @return BelongsToMany<CourseClass, $this>
     */
    public function teachingCourseClasses(): BelongsToMany
    {
        return $this->belongsToMany(CourseClass::class, 'class_instructors')
            ->withPivot(['role', 'is_primary', 'share_percentage'])
            ->withTimestamps();
    }
}
