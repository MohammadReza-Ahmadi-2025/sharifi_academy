<?php

namespace App\Support\Http;

use App\Support\SeoSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CanonicalIdSlugRedirect
{
    /**
     * Resolve by stable id; if the URL slug is wrong/outdated, 301 to the canonical id/slug URL.
     */
    public static function awayIfSlugMismatched(
        Request $request,
        Model $model,
        string $routeName,
        string $idParameter,
        string $requestedSlug,
    ): ?RedirectResponse {
        $canonicalSlug = SeoSlug::forUrl((string) $model->getAttribute('slug'));
        $requested = rawurldecode($requestedSlug);

        if ($requested === $canonicalSlug) {
            return null;
        }

        $url = route($routeName, [
            $idParameter => $model->getKey(),
            'slug' => $canonicalSlug,
        ]);

        $query = $request->getQueryString();
        if ($query !== null && $query !== '') {
            $url .= '?'.$query;
        }

        return redirect()->to($url, 301);
    }
}
