<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirect www ↔ apex (and http → https when APP_URL is https) so session/XSRF cookies stay consistent.
 */
class EnsureCanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->isProduction() || app()->runningUnitTests()) {
            return $next($request);
        }

        $appUrl = (string) config('app.url');
        $canonicalHost = parse_url($appUrl, PHP_URL_HOST);
        $canonicalScheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'https';

        if (! is_string($canonicalHost) || $canonicalHost === '') {
            return $next($request);
        }

        $requestHost = $request->getHost();
        $schemeMismatch = $canonicalScheme === 'https' && ! $request->secure();
        $hostMismatch = strcasecmp($requestHost, $canonicalHost) !== 0;

        if (! $schemeMismatch && ! $hostMismatch) {
            return $next($request);
        }

        $target = $canonicalScheme.'://'.$canonicalHost.$request->getRequestUri();

        return redirect()->away($target, 301);
    }
}
