<?php

namespace App\Support\Search;

use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Courses\Models\Course;
use App\Support\Search\Console\ImportSiteSearchCommand;
use App\Support\Search\Observers\SiteSearchIndexObserver;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\ModelObserver;

class SearchServiceProvider extends ServiceProvider
{
    /**
     * @var list<class-string>
     */
    private const SEARCHABLE_MODELS = [
        Product::class,
        Brand::class,
        Category::class,
        Course::class,
        Post::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $observer = SiteSearchIndexObserver::class;

        foreach (self::SEARCHABLE_MODELS as $modelClass) {
            ModelObserver::disableSyncingFor($modelClass);
            $modelClass::observe($observer);
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportSiteSearchCommand::class,
            ]);
        }
    }
}
