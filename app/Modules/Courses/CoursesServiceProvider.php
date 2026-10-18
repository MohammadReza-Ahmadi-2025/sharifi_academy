<?php

namespace App\Modules\Courses;

use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Courses\Policies\ClassSessionPolicy;
use App\Modules\Courses\Policies\CourseClassPolicy;
use App\Modules\Courses\Policies\CoursePolicy;
use App\Modules\Courses\Policies\EnrollmentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class CoursesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        Gate::policy(Course::class, CoursePolicy::class);
        Gate::policy(Enrollment::class, EnrollmentPolicy::class);
        Gate::policy(ClassSession::class, ClassSessionPolicy::class);
        Gate::policy(CourseClass::class, CourseClassPolicy::class);
    }
}
