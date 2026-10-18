<?php

namespace App\Modules\Blog;

use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Policies\PostPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class BlogServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        Gate::policy(Post::class, PostPolicy::class);
    }
}
