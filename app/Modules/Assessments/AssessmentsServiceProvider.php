<?php

namespace App\Modules\Assessments;

use App\Modules\Assessments\Models\Certificate;
use App\Modules\Assessments\Models\Quiz;
use App\Modules\Assessments\Policies\CertificatePolicy;
use App\Modules\Assessments\Policies\QuizPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AssessmentsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        Gate::policy(Quiz::class, QuizPolicy::class);
        Gate::policy(Certificate::class, CertificatePolicy::class);
    }
}
