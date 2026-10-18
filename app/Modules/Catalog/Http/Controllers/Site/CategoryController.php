<?php

namespace App\Modules\Catalog\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Services\CategoryBrowseService;
use App\Modules\Catalog\Support\CategoryPublicPresenter;
use App\Modules\Catalog\Support\CategorySeo;
use App\Support\Http\CanonicalSeoSlugRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryBrowseService $browse) {}

    public function show(Request $request, Category $category): Response|RedirectResponse
    {
        $redirect = CanonicalSeoSlugRedirect::awayIfMismatched(
            $request,
            $category,
            'categories.show',
            'category',
            2,
        );
        if ($redirect !== null) {
            return $redirect;
        }

        $canonicalSlug = $category->publicSlug();
        $requestedSlug = rawurldecode((string) $request->segment(2));
        if ($requestedSlug !== '' && $requestedSlug !== $canonicalSlug) {
            $url = route('categories.show', ['category' => $canonicalSlug]);
            $query = $request->getQueryString();
            if ($query !== null && $query !== '') {
                $url .= '?'.$query;
            }

            return redirect()->to($url, 301);
        }

        if ($this->shouldReturnItemsOnly($request)) {
            return Inertia::render('Public/Categories/Show', [
                'items' => $this->browse->paginateItemsOnly($category, $request)['items'],
            ]);
        }

        $listing = $this->browse->forPublicCategory($category, $request);

        $categoryPayload = CategoryPublicPresenter::forPage($category);
        $seo = $categoryPayload['seo'];
        $canonical = (string) $seo['canonical'];

        return Inertia::render('Public/Categories/Show', [
            'category' => $categoryPayload,
            'listing' => $listing['listing'],
            'items_label' => $listing['items_label'],
            'items' => $listing['items'],
            'subcategories' => $listing['subcategories'],
            'filters' => $listing['filters'],
            'filterOptions' => $listing['filterOptions'],
            'structuredData' => [
                'breadcrumb' => CategorySeo::breadcrumbJsonLd($category),
                'itemList' => CategorySeo::itemListJsonLd($category, $listing['items'], $canonical),
            ],
        ]);
    }

    private function shouldReturnItemsOnly(Request $request): bool
    {
        if (! $request->header('X-Inertia')) {
            return false;
        }

        $partial = (string) $request->header('X-Inertia-Partial-Data', '');

        return str_contains($partial, 'items') && (int) $request->query('page', 1) > 1;
    }
}
