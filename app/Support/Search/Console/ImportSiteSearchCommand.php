<?php

namespace App\Support\Search\Console;

use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Courses\Models\Course;
use Illuminate\Console\Command;

class ImportSiteSearchCommand extends Command
{
    protected $signature = 'site:search:import {--fresh : Flush indexes before importing}';

    protected $description = 'Import courses, brands, categories, products, and blog posts into Meilisearch';

    public function handle(): int
    {
        if (config('scout.driver') !== 'meilisearch') {
            $this->warn('SCOUT_DRIVER is not meilisearch; import skipped.');

            return self::SUCCESS;
        }

        $this->call('scout:sync-index-settings');

        $models = [
            'products' => Product::class,
            'brands' => Brand::class,
            'categories' => Category::class,
            'courses' => Course::class,
            'posts' => Post::class,
        ];

        $fresh = (bool) $this->option('fresh');

        foreach ($models as $label => $class) {
            $this->components->task("Importing {$label}", function () use ($class, $fresh): void {
                if ($fresh) {
                    $class::removeAllFromSearch();
                }

                $class::makeAllSearchable();
            });
        }

        $this->components->info('Site search indexes imported (courses, products, brands, categories, posts).');

        return self::SUCCESS;
    }
}
