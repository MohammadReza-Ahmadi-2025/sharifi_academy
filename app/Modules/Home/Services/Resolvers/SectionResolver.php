<?php

namespace App\Modules\Home\Services\Resolvers;

use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Courses\Models\Course;
use App\Modules\Home\Models\Banner;
use App\Modules\Home\Models\HomeSection;
use App\Modules\Home\Services\HomeCacheInvalidator;
use App\Modules\Home\Support\HomeItemPresenter;
use Illuminate\Support\Facades\Cache;

class SectionResolver
{
    public function __construct(private HomeCacheInvalidator $cache) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function resolve(): array
    {
        $sections = Cache::remember($this->cache->sectionsSkeletonKey(), 600, function () {
            return HomeSection::query()
                ->where('status', true)
                ->orderBy('sort')
                ->with([
                    'tabs' => function ($query) {
                        $query->where('is_active', true)
                            ->orderBy('sort_order')
                            ->with([
                                'items' => function ($itemsQuery) {
                                    $itemsQuery->orderBy('sort_order')
                                        ->with([
                                            'item' => function ($morph) {
                                                $morph->morphWith([
                                                    Banner::class => ['file'],
                                                    Product::class => ['mainImage'],
                                                    Course::class => HomeItemPresenter::courseEagerRelations(),
                                                    Post::class => ['featuredImage'],
                                                    Brand::class => [],
                                                    Category::class => ['image'],
                                                ]);
                                            },
                                        ]);
                                },
                            ]);
                    },
                ])
                ->get();
        });

        return $sections
            ->map(function (HomeSection $section) {
                $tabs = $section->tabs
                    ->map(fn ($tab) => app(TabResolver::class)->resolve($tab))
                    ->filter()
                    ->values()
                    ->all();

                if ($tabs === []) {
                    return null;
                }

                return [
                    'id' => $section->id,
                    'title' => $section->title,
                    'settings' => $section->settings ?? [],
                    'tabs' => $tabs,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
