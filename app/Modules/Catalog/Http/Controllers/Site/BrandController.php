<?php

namespace App\Modules\Catalog\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Support\BrandSeo;
use App\Modules\Catalog\Support\ProductCatalogPresenter;
use App\Support\Search\SiteSearchUrls;
use App\Support\SeoSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BrandController extends Controller
{
    public function show(Request $request, string $brand): Response|RedirectResponse
    {
        $model = SeoSlug::resolveModel(Brand::query()->where('status', true), $brand, 'slug');
        abort_if($model === null, 404);

        $canonicalSlug = SeoSlug::forUrl((string) $model->slug);
        $requestedSlug = rawurldecode($brand);
        if ($requestedSlug !== '' && $requestedSlug !== $canonicalSlug) {
            $url = url(SiteSearchUrls::brand($model));
            $query = $request->getQueryString();
            if ($query !== null && $query !== '') {
                $url .= '?'.$query;
            }

            return redirect()->to($url, 301);
        }

        $model->recordVisit();

        $products = $model->products()
            ->active()
            ->visible()
            ->with(['mainImage', 'brand'])
            ->orderByDesc('id')
            ->limit(48)
            ->get()
            ->map(fn ($product) => ProductCatalogPresenter::forCard($product))
            ->values();

        $appName = (string) config('app.name');
        $seo = BrandSeo::metaForBrand($model);

        return Inertia::render('Public/BrandShow', [
            'brand' => [
                'id' => $model->id,
                'title' => $model->title,
                'slug' => $canonicalSlug,
                'description' => $model->description,
                'website' => $model->website,
                'seo' => $seo,
            ],
            'products' => $products,
            'structuredData' => [
                'breadcrumb' => BrandSeo::breadcrumbJsonLd($model),
                'brand' => BrandSeo::brandJsonLd($model),
                'webPage' => BrandSeo::collectionPageJsonLd($seo, $appName),
                'itemList' => BrandSeo::itemListJsonLd($model, $products),
            ],
        ]);
    }
}
