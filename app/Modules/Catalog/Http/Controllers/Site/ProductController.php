<?php

namespace App\Modules\Catalog\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Support\ProductPublicPresenter;
use App\Modules\Catalog\Support\ProductSeo;
use App\Support\Http\CanonicalSeoSlugRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function show(Request $request, Product $product): Response|RedirectResponse
    {
        ProductPublicPresenter::ensureVisible($product);

        $redirect = CanonicalSeoSlugRedirect::awayIfMismatched(
            $request,
            $product,
            'books.show',
            'product',
            2,
        );
        if ($redirect !== null) {
            return $redirect;
        }

        $canonicalSlug = $product->publicSlug();
        $requestedSlug = rawurldecode((string) $request->segment(2));
        if ($requestedSlug !== '' && $requestedSlug !== $canonicalSlug) {
            $url = route('books.show', ['product' => $canonicalSlug]);
            $query = $request->getQueryString();
            if ($query !== null && $query !== '') {
                $url .= '?'.$query;
            }

            return redirect()->to($url, 301);
        }

        $product->recordVisit();
        $payload = ProductPublicPresenter::forPage($product);
        $appName = (string) config('app.name');

        return Inertia::render('Public/BookShow', [
            'product' => $payload,
            'structuredData' => [
                'breadcrumb' => ProductSeo::breadcrumbJsonLd($product),
                'product' => ProductSeo::productJsonLd($product, $appName),
                'webPage' => ProductSeo::webPageJsonLd($product, $appName),
            ],
        ]);
    }
}
