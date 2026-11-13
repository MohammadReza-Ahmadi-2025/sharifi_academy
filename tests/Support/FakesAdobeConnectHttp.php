<?php

namespace Tests\Support;

use Illuminate\Support\Facades\Http;

trait FakesAdobeConnectHttp
{
    /**
     * @return mixed
     */
    protected function adobeAuthResponse(string $url)
    {
        if (str_contains($url, 'action=common-info')) {
            return Http::response(
                '<?xml version="1.0"?><results><status code="ok"/><cookie>fake-session</cookie></results>',
            );
        }

        if (str_contains($url, 'action=login')) {
            return Http::response(
                '<?xml version="1.0"?><results><status code="ok"/></results>',
                200,
                ['Set-Cookie' => 'BREEZESESSION=fake-session; Path=/'],
            );
        }

        return null;
    }
}
