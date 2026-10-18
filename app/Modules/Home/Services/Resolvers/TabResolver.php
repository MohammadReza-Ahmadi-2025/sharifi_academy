<?php

namespace App\Modules\Home\Services\Resolvers;

use App\Models\User;
use App\Modules\Profile\Http\Controllers\Site\InstructorResumeController;
use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Courses\Models\Course;
use App\Modules\Home\Models\Banner;
use App\Modules\Home\Models\HomeSectionTab;
use App\Modules\Home\Services\HomeCacheInvalidator;
use App\Modules\Home\Support\HomeItemPresenter;
use App\Modules\Profile\Enums\ResumeModerationStatus;
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
            if (in_array($tab->content_type, ['features', 'testimonials'], true)) {
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

            $manualOrder = $this->applySource($query, $tab);

            if ($tab->source_type === 'manual') {
                $items = $query->get();

                if ($manualOrder !== []) {
                    $positions = array_flip($manualOrder);
                    $items = $items
                        ->sortBy(fn ($model) => $positions[$model->getKey()] ?? PHP_INT_MAX)
                        ->values();
                }
            } else {
                $limit = (int) ($tab->limit ?? 10);

                $items = $query->limit($limit > 0 ? $limit : 10)->get();
            }

            if ($tab->content_type === 'instructor') {
                $items = $items
                    ->filter(
                        fn ($item) => $item instanceof User && InstructorResumeController::isPublicInstructor($item),
                    )
                    ->values();
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

        if ($tab->content_type === 'testimonials') {
            $payload['testimonials'] = data_get($tab->settings, 'testimonials', []);
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
                ->when(
                    $tab->source_type !== 'manual',
                    fn (Builder $builder) => $builder->whereNull('parent_id'),
                )
                ->whereIn('type', [Category::TYPE_PRODUCT, Category::TYPE_COURSE])
                ->with('image'),

            'post' => Post::query()
                ->published()
                ->with('featuredImage'),

            'instructor' => User::query()
                ->role('instructor')
                ->where('status', 1)
                ->whereHas('profile', fn ($q) => $q->where('resume_moderation_status', ResumeModerationStatus::Approved->value))
                ->with('profile'),

            default => null,
        };
    }

    /**
     * @return list<int> Manual item ids in display order (empty when not manual).
     */
    protected function applySource(Builder $query, HomeSectionTab $tab): array
    {
        switch ($tab->source_type) {
            case 'latest':
                if ($tab->content_type === 'instructor') {
                    $query->orderByDesc('id');
                } elseif ($tab->content_type === 'course') {
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

                    return [];
                }

                $query->whereIn('id', $ids);

                return $ids;
        }

        return [];
    }
}
