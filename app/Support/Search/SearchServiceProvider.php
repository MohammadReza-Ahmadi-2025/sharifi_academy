<?php

namespace App\Support\Search;

use App\Models\User;
use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Courses\Models\Course;
use App\Modules\Profile\Models\UserProfile;
use App\Support\Search\Console\ImportSiteSearchCommand;
use App\Support\Search\Observers\SiteSearchIndexObserver;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\EngineManager;
use Laravel\Scout\ModelObserver;
use Meilisearch\Client as MeilisearchClient;
use Throwable;

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
        User::class,
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

        // Profile visibility / headline live on user_profiles — reindex the parent user.
        UserProfile::saved(function (UserProfile $profile): void {
            $user = $profile->user;
            if (! $user instanceof User) {
                return;
            }

            try {
                if ($user->shouldBeSearchable()) {
                    $user->searchable();
                } else {
                    $user->unsearchable();
                }
            } catch (Throwable $e) {
                Log::warning('Scout user reindex after profile save failed; request continues.', [
                    'user_id' => $user->id,
                    'message' => $e->getMessage(),
                ]);
            }
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportSiteSearchCommand::class,
            ]);
        }
    }
}
