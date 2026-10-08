<?php

namespace App\Modules\Home\Services\Resolvers;

use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Courses\Models\Course;
use App\Modules\Home\Models\Banner;
use App\Modules\Home\Models\HomeSectionTab;
use App\Modules\Home\Services\HomeCacheInvalidator;
use App\Modules\Home\Support\HomeItemPresenter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class TabResolver
{
    public function __construct(private HomeCacheInvalidator $cache) {}

    /**
     * @return array<string, mixed>|null
     */
    public function resolve(HomeSectionTab $tab): ?array
    {
        return Cache::remember($this->cache->tabKey($tab->id), 600, function () use ($tab) {
            if ($tab->content_type === 'features') {
                return $this->formatResponse($tab, collect());
            }

            if ($tab->content_type === 'banner' && $tab->source_type === 'manual') {
                $tab->loadMissing([
                    'items' => fn ($query) => $query->orderBy('sort_order'),
                    'items.item' => fn ($morph) => $morph->morphWith([
                        Banner::class => ['file'],
                    ]),
                ]);

                $banners = $tab->items
                    ->sortBy('sort_order')
                    ->map(fn ($row) => $row->item)
                    ->filter(fn ($model) => $model instanceof Banner)
                    ->values();

                if ($tab->limit) {
                    $banners = $banners->take($tab->limit)->values();
                }

                return $this->formatResponse($tab, $banners);
            }

            $query = $this->baseQuery($tab);

            if (! $query) {
                return null;
            }

            $this->applySource($query, $tab);

            if ($tab->source_type === 'manual') {
                $items = $query->get();
            } else {
                $limit = (int) ($tab->limit ?? 10);

                $items = $query->limit($limit > 0 ? $limit : 10)->get();
            }

            return $this->formatResponse($tab, $items);
        });
    }

    /**
     * @param  Collection<int, mixed>  $items
     * @return array<string, mixed>
     */
    protected function formatResponse(HomeSectionTab $tab, $items): array
    {
        $payload = [
            'id' => $tab->id,
            'title' => $tab->title,
            'content_type' => $tab->content_type,
            'title_url' => data_get($tab->settings, 'title_url'),
            'view_all_url' => data_get($tab->settings, 'view_all_url'),
            'items' => HomeItemPresenter::present(
                $items,
                $tab->content_type === 'banner' ? 'banner' : $tab->content_type,
            ),
        ];

        if ($tab->content_type === 'features') {
            $payload['features'] = data_get($tab->settings, 'features', []);
        }

        return $payload;
    }

    /**
     * @return Builder|null
     */
    protected function baseQuery(HomeSectionTab $tab)
    {
        return match ($tab->content_type) {
            'product' => Product::query()
                ->active()
                ->visible()
                ->withCardPricing()
                ->with('mainImage'),

            'course' => Course::query()
                ->published()
                ->with(HomeItemPresenter::courseEagerRelations()),

            'brand' => Brand::query()
                ->where('status', true),

            'category' => Category::query()
                ->active()
                ->whereNull('parent_id')
                ->product()
                ->with('image'),

            'post' => Post::query()
                ->published()
                ->with('featuredImage'),

            default => null,
        };
    }

    protected function applySource(Builder $query, HomeSectionTab $tab): void
    {
        switch ($tab->source_type) {
            case 'latest':
                if ($tab->content_type === 'course') {
                    $query->orderedForDisplay();
                } elseif ($tab->content_type === 'product') {
                    $query->orderBy('sort')->orderByDesc('id');
                } else {
                    $query->latest();
                }
                break;

            case 'oldest':
                if ($tab->content_type === 'post') {
                    $query->oldest('published_at');
                } else {
                    $query->oldest();
                }
                break;

            case 'best_selling':
                $query->orderByDesc('sales_count');
                break;

            case 'most_viewed':
                $query->orderByDesc('views');
                break;

            case 'by_category':
                $categoryId = data_get($tab->settings, 'category_id');

                if ($categoryId) {
                    if ($tab->content_type === 'product') {
                        $query->whereHas('categories', fn ($builder) => $builder->where('categories.id', $categoryId));
                    } elseif ($tab->content_type === 'course') {
                        $query->whereHas('categories', fn ($builder) => $builder->where('categories.id', $categoryId));
                    } else {
                        $query->where('category_id', $categoryId);
                    }
                }
                break;

            case 'by_brand':
                $brandId = data_get($tab->settings, 'brand_id');

                if ($brandId) {
                    $query->where('brand_id', $brandId);
                }
                break;

            case 'manual':
                $tab->loadMissing([
                    'items' => fn ($q) => $q->orderBy('sort_order'),
                ]);

                $ids = $tab->items
                    ->sortBy('sort_order')
                    ->pluck('item_id')
                    ->filter()
                    ->values()
                    ->map(fn ($id) => (int) $id)
                    ->all();

                if ($ids === []) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->whereIn('id', $ids)
                    ->orderByRaw('FIELD(id,'.implode(',', $ids).')');
                break;
        }
    }
}
