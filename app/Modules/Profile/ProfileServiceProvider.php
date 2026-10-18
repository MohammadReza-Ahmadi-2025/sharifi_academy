<?php

namespace App\Modules\Profile;

use App\Modules\Profile\Models\Resume;
use App\Modules\Profile\Policies\ResumePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ProfileServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        Gate::policy(Resume::class, ResumePolicy::class);
    }
}
