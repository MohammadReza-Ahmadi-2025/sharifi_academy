<?php

namespace App\Providers;

use App\Modules\Activity\ActivityServiceProvider;
use App\Modules\Admin\AdminServiceProvider;
use App\Modules\AdobeConnect\AdobeConnectServiceProvider;
use App\Modules\Assessments\AssessmentsServiceProvider;
use App\Modules\Auth\AuthServiceProvider;
use App\Modules\Blog\BlogServiceProvider;
use App\Modules\Cart\CartServiceProvider;
use App\Modules\Catalog\CatalogServiceProvider;
use App\Modules\Comments\CommentsServiceProvider;
use App\Modules\Courses\CoursesServiceProvider;
use App\Modules\Files\FilesServiceProvider;
use App\Modules\Home\HomeServiceProvider;
use App\Modules\Locations\LocationsServiceProvider;
use App\Modules\Menu\MenuServiceProvider;
use App\Modules\Pages\PagesServiceProvider;
use App\Modules\Profile\ProfileServiceProvider;
use App\Modules\Roles\RolesServiceProvider;
use App\Modules\Settings\SettingsServiceProvider;
use App\Modules\Social\SocialServiceProvider;
use App\Modules\Stories\StoriesServiceProvider;
use App\Modules\Users\UsersServiceProvider;
use App\Modules\Visits\VisitsServiceProvider;
use App\Support\Search\SearchServiceProvider;
use Illuminate\Support\ServiceProvider;

class ModulesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(AuthServiceProvider::class);
        $this->app->register(ActivityServiceProvider::class);
        $this->app->register(AdminServiceProvider::class);
        $this->app->register(RolesServiceProvider::class);
        $this->app->register(UsersServiceProvider::class);
        $this->app->register(LocationsServiceProvider::class);
        $this->app->register(CatalogServiceProvider::class);
        $this->app->register(SearchServiceProvider::class);
        $this->app->register(CartServiceProvider::class);
        $this->app->register(FilesServiceProvider::class);
        $this->app->register(HomeServiceProvider::class);
        $this->app->register(MenuServiceProvider::class);
        $this->app->register(VisitsServiceProvider::class);
        $this->app->register(CoursesServiceProvider::class);
        $this->app->register(AssessmentsServiceProvider::class);
        $this->app->register(AdobeConnectServiceProvider::class);
        $this->app->register(ProfileServiceProvider::class);
        $this->app->register(SocialServiceProvider::class);
        $this->app->register(CommentsServiceProvider::class);
        $this->app->register(BlogServiceProvider::class);
        $this->app->register(PagesServiceProvider::class);
        $this->app->register(StoriesServiceProvider::class);
        $this->app->register(SettingsServiceProvider::class);
    }
}
