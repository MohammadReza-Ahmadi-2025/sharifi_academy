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
use Laravel\Scout\EngineManager;
use Laravel\Scout\ModelObserver;
use Meilisearch\Client as MeilisearchClient;

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
        $this->app->make(EngineManager::class)->extend('meilisearch', function ($app) {
            return new SoftFailMeilisearchEngine(
                $app->make(MeilisearchClient::class),
                (bool) config('scout.soft_delete', false),
                (array) config('scout.meilisearch', []),
            );
        });

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
