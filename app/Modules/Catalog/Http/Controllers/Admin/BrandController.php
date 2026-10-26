<?php

namespace App\Modules\Catalog\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Http\Requests\Admin\StoreBrandRequest;
use App\Modules\Catalog\Http\Requests\Admin\UpdateBrandRequest;
use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Support\BrandPresenter;
use App\Support\SeoSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BrandController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('catalog-brands-index'), 403);

        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');
        $perPage = in_array((int) $request->query('per_page', 25), [25, 50, 100], true)
            ? (int) $request->query('per_page', 25)
            : 25;

        $query = Brand::query()->orderBy('sort')->orderBy('title');

        if ($status === '1' || $status === '0') {
            $query->where('status', $status === '1');
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $brands = $query->paginate($perPage)->withQueryString()
            ->through(fn (Brand $brand) => BrandPresenter::forList($brand));

        $actor = $request->user();

        return Inertia::render('Admin/Catalog/Brands/Index', [
            'brands' => $brands,
            'filters' => [
                'search' => $search,
                'status' => is_string($status) ? $status : '',
                'per_page' => $perPage,
            ],
            'abilities' => [
                'create' => $actor?->can('catalog-brands-create') ?? false,
                'update' => $actor?->can('catalog-brands-update') ?? false,
                'delete' => $actor?->can('catalog-brands-delete') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()?->can('catalog-brands-create'), 403);

        return Inertia::render('Admin/Catalog/Brands/Create');
    }

    public function store(StoreBrandRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $slug = $this->uniqueSlug($data['slug'] ?? $data['title']);

        $brand = Brand::query()->create([
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'website' => $data['website'] ?? null,
            'status' => $request->boolean('status', true),
            'sort' => (int) ($data['sort'] ?? ((int) Brand::query()->max('sort') + 1)),
            ...$this->seoPayload($data),
        ]);

        return redirect()
            ->route('admin.catalog.brands.edit', $brand)
            ->with('success', 'برند ایجاد شد.');
    }

    public function edit(Request $request, Brand $brand): Response
    {
        abort_unless($request->user()?->can('catalog-brands-update'), 403);

        return Inertia::render('Admin/Catalog/Brands/Edit', [
            'brand' => BrandPresenter::forForm($brand),
        ]);
    }

    public function update(UpdateBrandRequest $request, Brand $brand): RedirectResponse
    {
        $data = $request->validated();
        $slug = $this->uniqueSlug(
            (string) ($data['slug'] ?? $brand->slug),
            $brand->id,
        );

        $brand->update([
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'website' => $data['website'] ?? null,
            'status' => $request->boolean('status'),
            'sort' => (int) ($data['sort'] ?? $brand->sort),
            ...$this->seoPayload($data),
        ]);

        return redirect()
            ->route('admin.catalog.brands.edit', $brand)
            ->with('success', 'برند به‌روزرسانی شد.');
    }

    public function destroy(Request $request, Brand $brand): RedirectResponse
    {
        abort_unless($request->user()?->can('catalog-brands-delete'), 403);

        if ($brand->products()->exists()) {
            return back()->withErrors(['brand' => 'این برند به محصولات متصل است و قابل حذف نیست.']);
        }

        $brand->delete();

        return redirect()
            ->route('admin.catalog.brands.index')
            ->with('success', 'برند حذف شد.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function seoPayload(array $data): array
    {
        return [
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
            'canonical' => $data['canonical'] ?? null,
        ];
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = SeoSlug::normalize($base);
        if ($slug === '') {
            $slug = SeoSlug::normalize(Str::random(8));
        }
        $original = $slug;
        $counter = 1;

        while (Brand::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $original.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
