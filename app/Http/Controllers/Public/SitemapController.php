<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\Sitemap\SiteSitemapBuilder;
use App\Support\Sitemap\SitemapUrl;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(SiteSitemapBuilder $builder): Response
    {
        $urls = $builder->urls();

        $xml = view('sitemap', [
            'urls' => array_map(
                static fn (SitemapUrl $url) => $url->toArray(),
                $urls,
            ),
        ])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
