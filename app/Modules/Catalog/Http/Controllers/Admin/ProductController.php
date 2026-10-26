<?php

namespace App\Modules\Catalog\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Enums\VariantDisplayMode;
use App\Modules\Catalog\Http\Requests\Admin\StoreProductRequest;
use App\Modules\Catalog\Http\Requests\Admin\UpdateProductRequest;
use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Services\AdminProductVariantsSyncService;
use App\Modules\Catalog\Support\AdminCatalogEnumOptions;
use App\Modules\Catalog\Support\ProductPresenter;
use App\Modules\Catalog\Support\VariantAttributeCatalog;
use App\Modules\Files\Http\Concerns\SyncsMorphFilesFromRequest;
use App\Support\SeoSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    use SyncsMorphFilesFromRequest;

    public function __construct(
        private readonly AdminProductVariantsSyncService $variantsSync,
    ) {}

    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('catalog-products-index'), 403);

        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');
        $visible = $request->query('visible');
        $perPage = in_array((int) $request->query('per_page', 25), [25, 50, 100], true)
            ? (int) $request->query('per_page', 25)
            : 25;

        $query = Product::query()
            ->with(['brand'])
            ->withCount('variants')
            ->orderBy('sort')
            ->orderByDesc('created_at');

        if ($status === '1' || $status === '0') {
            $query->where('status', $status === '1');
        }

        if ($visible === '1' || $visible === '0') {
            $query->where('is_visible', $visible === '1');
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query->paginate($perPage)->withQueryString()
            ->through(fn (Product $product) => ProductPresenter::forList($product));

        $actor = $request->user();

        return Inertia::render('Admin/Catalog/Products/Index', [
            'products' => $products,
            'filters' => [
                'search' => $search,
                'status' => is_string($status) ? $status : '',
                'visible' => is_string($visible) ? $visible : '',
                'per_page' => $perPage,
            ],
            'abilities' => [
                'create' => $actor?->can('catalog-products-create') ?? false,
                'update' => $actor?->can('catalog-products-update') ?? false,
                'delete' => $actor?->can('catalog-products-delete') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()?->can('catalog-products-create'), 403);

        return Inertia::render('Admin/Catalog/Products/Create', [
            ...$this->formOptions(),
            'displayModeOptions' => AdminCatalogEnumOptions::fromEnum(VariantDisplayMode::class),
            'variantAttributes' => VariantAttributeCatalog::forAdmin(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $slug = $this->uniqueProductSlug($data['slug'] ?? $data['title']);

        $product = Product::query()->create([
            ...$this->productPayload($data, $slug),
        ]);

        $product->categories()->sync($data['category_ids'] ?? []);
        $this->syncGalleryFromRequest($product, $request);
        $this->variantsSync->sync(
            $product,
            $request->productVariantsPayload(),
            $request->user()?->id,
        );

        return redirect()
            ->route('admin.catalog.products.edit', $product)
            ->with('success', 'محصول ایجاد شد.');
    }

    public function edit(Request $request, Product $product): Response
    {
        abort_unless($request->user()?->can('catalog-products-update'), 403);

        $variants = $product->variants()
            ->where('status', true)
            ->with(['currentPrice', 'inventory', 'attributeValues.attribute'])
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(fn ($variant) => ProductPresenter::variantForAdmin($variant));

        $actor = $request->user();

        return Inertia::render('Admin/Catalog/Products/Edit', [
            'product' => ProductPresenter::forForm($product),
            'variants' => $variants,
            ...$this->formOptions(),
            'displayModeOptions' => AdminCatalogEnumOptions::fromEnum(VariantDisplayMode::class),
            'variantAttributes' => VariantAttributeCatalog::forAdmin(),
            'abilities' => [
                'variants' => $actor?->can('catalog-products-update') ?? false,
                'delete' => $actor?->can('catalog-products-delete') ?? false,
            ],
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        $slug = $this->uniqueProductSlug(
            (string) ($data['slug'] ?? $product->slug),
            $product->id,
        );

        $product->update([
            ...$this->productPayload($data, $slug),
        ]);

        $product->categories()->sync($data['category_ids'] ?? []);
        $this->syncGalleryFromRequest($product, $request);
        $this->variantsSync->sync(
            $product,
            $request->productVariantsPayload(),
            $request->user()?->id,
        );

        return redirect()
            ->route('admin.catalog.products.edit', $product)
            ->with('success', 'محصول به‌روزرسانی شد.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        abort_unless($request->user()?->can('catalog-products-delete'), 403);

        $product->delete();

        return redirect()
            ->route('admin.catalog.products.index')
            ->with('success', 'محصول حذف شد.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        $categories = Category::query()
            ->product()
            ->orderBy('sort')
            ->orderBy('title')
            ->get()
            ->map(fn (Category $category) => [
                'value' => $category->id,
                'label' => $category->title,
            ])
            ->values()
            ->all();

        $brands = Brand::query()
            ->orderBy('sort')
            ->orderBy('title')
            ->get()
            ->map(fn (Brand $brand) => [
                'value' => $brand->id,
                'label' => $brand->title,
            ])
            ->values()
            ->all();

        return [
            'categoryOptions' => $categories,
            'brandOptions' => $brands,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function productPayload(array $data, string $slug): array
    {
        return [
            'title' => $data['title'],
            'slug' => $slug,
            'sku' => $data['sku'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'] ?? null,
            'features' => $data['features'] ?? null,
            'brand_id' => $data['brand_id'] ?? null,
            'model' => $data['model'] ?? null,
            'status' => $data['status'] ?? true,
            'is_visible' => $data['is_visible'] ?? true,
            'sort' => $data['sort'] ?? 0,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
            'canonical' => $data['canonical'] ?? null,
        ];
    }

    private function uniqueProductSlug(string $slug, ?int $ignoreId = null): string
    {
        $slug = SeoSlug::normalize($slug);
        if ($slug === '') {
            $slug = 'product';
        }

        $baseSlug = $slug;
        $counter = 1;

        while (Product::query()
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }

        return $slug;
    }
}
