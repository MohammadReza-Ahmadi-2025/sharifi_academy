<?php

namespace App\Modules\Pages\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Support\PagePublicPresenter;
use App\Modules\Pages\Support\PageSeo;
use App\Modules\Settings\Services\SiteSettingsService;
use App\Support\Http\CanonicalSeoSlugRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function show(Request $request, Page $page): Response|RedirectResponse
    {
        PagePublicPresenter::ensurePublic($page);

        $redirect = CanonicalSeoSlugRedirect::awayIfMismatched(
            $request,
            $page,
            'pages.show',
            'page',
            2,
        );
        if ($redirect !== null) {
            return $redirect;
        }

        $canonicalSlug = $page->publicSlug();
        $requestedSlug = rawurldecode((string) $request->segment(2));
        if ($requestedSlug !== '' && $requestedSlug !== $canonicalSlug) {
            $url = route('pages.show', ['page' => $canonicalSlug]);
            $query = $request->getQueryString();
            if ($query !== null && $query !== '') {
                $url .= '?'.$query;
            }

            return redirect()->to($url, 301);
        }

        $settings = app(SiteSettingsService::class);
        $appName = (string) ($settings->get('site_name') ?: config('app.name'));
        $payload = PagePublicPresenter::forPage($page, $settings, $appName);
        $seo = $payload['seo'];

        return Inertia::render('Public/Page/Show', [
            'page' => $payload,
            'structuredData' => [
                'breadcrumb' => PageSeo::breadcrumbJsonLd($page),
                'webPage' => PageSeo::webPageJsonLd($seo, $page),
            ],
        ]);
    }
}
