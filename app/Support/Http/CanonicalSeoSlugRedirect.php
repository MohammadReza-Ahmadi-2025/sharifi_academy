<?php

namespace App\Support\Http;

use App\Support\SeoSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CanonicalSeoSlugRedirect
{
    public static function awayIfMismatched(
        Request $request,
        Model $model,
        string $routeName,
        string $routeParameter,
        int $segmentIndex = 2,
    ): ?RedirectResponse {
        $requested = rawurldecode((string) $request->segment($segmentIndex));
        $canonical = SeoSlug::forUrl((string) $model->getAttribute('slug'));

        if ($requested === '' || $requested === $canonical) {
            return null;
        }

        if (SeoSlug::normalize($requested) !== $canonical) {
            return null;
        }

        $url = route($routeName, [$routeParameter => $canonical]);
        $query = $request->getQueryString();
        if ($query !== null && $query !== '') {
            $url .= '?'.$query;
        }

        return redirect()->to($url, 301);
    }
}
