<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Support\ProductCatalogPresenter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProductCategoryBrowseService
{
    public const SORT_NEWEST = 'newest';

    public const SORT_TITLE = 'title';

    public const SORT_PRICE_ASC = 'price_asc';

    public const SORT_PRICE_DESC = 'price_desc';

    /**
     * @return array<string, mixed>
     */
    public function filtersFromRequest(Request $request): array
    {
        $sort = (string) $request->query('sort', self::SORT_NEWEST);
        if (! in_array($sort, [self::SORT_NEWEST, self::SORT_TITLE, self::SORT_PRICE_ASC, self::SORT_PRICE_DESC], true)) {
            $sort = self::SORT_NEWEST;
        }

        return [
            'q' => trim((string) $request->query('q', '')),
            'brand' => trim((string) $request->query('brand', '')),
            'sort' => $sort,
            'per_page' => $this->perPage($request),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateProductsInCategory(Category $category, array $filters): LengthAwarePaginator
    {
        $query = $category->products()
            ->active()
            ->visible()
            ->whereNull('products.deleted_at')
            ->with(['mainImage', 'brand']);

        $this->applyFilters($query, $filters);
        $this->applySort($query, (string) $filters['sort']);

        return $query
            ->paginate((int) $filters['per_page'])
            ->withQueryString()
            ->through(fn (Product $product) => ProductCatalogPresenter::forCard($product));
    }

    /**
     * @return Collection<int, Brand>
     */
    public function brandsInCategory(Category $category): Collection
    {
        return Brand::query()
            ->select('brands.id', 'brands.title', 'brands.slug')
            ->where('brands.status', true)
            ->whereHas('products', function (Builder $query) use ($category) {
                $query->active()
                    ->visible()
                    ->whereHas('categories', fn (Builder $categoryQuery) => $categoryQuery->whereKey($category->id));
            })
            ->orderBy('brands.sort')
            ->orderBy('brands.title')
            ->get();
    }

    /**
     * @param  Builder<Product>|BelongsToMany<Product, Category>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder|BelongsToMany $query, array $filters): void
    {
        $search = (string) ($filters['q'] ?? '');
        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->where('products.title', 'like', '%'.$search.'%')
                    ->orWhere('products.short_description', 'like', '%'.$search.'%')
                    ->orWhere('products.sku', 'like', '%'.$search.'%');
            });
        }

        $brandSlug = (string) ($filters['brand'] ?? '');
        if ($brandSlug !== '') {
            $query->whereHas('brand', fn (Builder $brandQuery) => $brandQuery->where('slug', $brandSlug));
        }
    }

    /**
     * @param  Builder<Product>|BelongsToMany<Product, Category>  $query
     */
    private function applySort(Builder|BelongsToMany $query, string $sort): void
    {
        if ($sort === self::SORT_TITLE) {
            $query->orderBy('products.title');

            return;
        }

        if ($sort === self::SORT_PRICE_ASC) {
            $query->orderBy('products.min_effective_price');

            return;
        }

        if ($sort === self::SORT_PRICE_DESC) {
            $query->orderByDesc('products.min_effective_price');

            return;
        }

        $query->orderBy('products.sort')->orderByDesc('products.id');
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function sortOptions(): array
    {
        return [
            ['value' => self::SORT_NEWEST, 'label' => 'جدیدترین'],
            ['value' => self::SORT_TITLE, 'label' => 'عنوان (الفبا)'],
            ['value' => self::SORT_PRICE_ASC, 'label' => 'ارزان‌ترین'],
            ['value' => self::SORT_PRICE_DESC, 'label' => 'گران‌ترین'],
        ];
    }

    private function perPage(Request $request): int
    {
        return 24;
    }
}
