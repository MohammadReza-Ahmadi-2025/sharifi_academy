<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Services\SiteSettingsService;
use App\Modules\Stories\Services\StoryFeedService;
use App\Services\Home\HomePageService;
use App\Support\Seo\SiteJsonLd;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(
        Request $request,
        HomePageService $homePage,
        StoryFeedService $storyFeed,
        SiteSettingsService $settings,
    ): Response {
        $payload = $homePage->data();
        $appName = (string) config('app.name');
        $jsonLd = SiteJsonLd::forHome($settings, $appName);

        return Inertia::render('Public/Home', [
            ...$payload,
            'storyGroups' => $storyFeed->groupsForViewer($request->user()),
            'seo' => SiteJsonLd::homeSeo($settings, $appName),
            'structuredData' => [
                'organization' => $jsonLd['organization'],
                'webSite' => $jsonLd['webSite'],
            ],
        ]);
    }
}
