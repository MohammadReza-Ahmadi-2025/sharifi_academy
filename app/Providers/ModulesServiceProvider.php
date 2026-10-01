<?php

namespace App\Providers;

use App\Modules\Admin\AdminServiceProvider;
use App\Modules\Auth\AuthServiceProvider;
use App\Modules\Catalog\CatalogServiceProvider;
use App\Modules\Courses\CoursesServiceProvider;
use App\Modules\Files\FilesServiceProvider;
use App\Modules\Locations\LocationsServiceProvider;
use App\Modules\Profile\ProfileServiceProvider;
use App\Modules\Roles\RolesServiceProvider;
use App\Modules\Users\UsersServiceProvider;
use Illuminate\Support\ServiceProvider;

class ModulesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(AuthServiceProvider::class);
        $this->app->register(AdminServiceProvider::class);
        $this->app->register(RolesServiceProvider::class);
        $this->app->register(UsersServiceProvider::class);
        $this->app->register(LocationsServiceProvider::class);
        $this->app->register(CatalogServiceProvider::class);
        $this->app->register(FilesServiceProvider::class);
        $this->app->register(CoursesServiceProvider::class);
        $this->app->register(ProfileServiceProvider::class);
    }
}
