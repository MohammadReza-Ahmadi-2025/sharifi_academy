<?php

namespace App\Modules\AdobeConnect\Services;

use App\Modules\AdobeConnect\Support\AdobeConnectConfig;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use SimpleXMLElement;

class AdobeConnectXmlClient
{
    public function __construct(
        private readonly AdobeConnectConfig $config,
    ) {}

    /**
     * @param  array<string, scalar|null>  $query
     */
    public function get(string $action, array $query = [], bool $authenticated = true): SimpleXMLElement
    {
        if ($authenticated) {
            $query['session'] = $this->session();
        }

        $query['action'] = $action;

        $response = $this->http()->get('/api/xml', $query);

        try {
            return $this->parseResponse($response, $action);
        } catch (RuntimeException $exception) {
            if (! $authenticated || ! $this->isSessionFailure($exception)) {
                throw $exception;
            }

            // فقط خطای سشن؛ invalid پارامتر سشن را خراب نمی‌کند.
            $this->forgetSession();
            $query['session'] = $this->session();

            return $this->parseResponse(
                $this->http()->get('/api/xml', $query),
                $action,
            );
        }
    }

    public function forgetSession(): void
    {
        Cache::forget($this->config->sessionCacheKey());
    }

    private function session(): string
    {
        if (! $this->config->isApiConfigured()) {
            throw new RuntimeException('Adobe Connect API is not configured (base URL, login, API token).');
        }

        $cacheKey = $this->config->sessionCacheKey();

        $cached = Cache::get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        // Enhanced Security (Hosted پیش‌فرض): common-info → login → common-info دوباره
        $preSession = $this->commonInfoSession();

        $loginResponse = $this->http()->get('/api/xml', array_filter([
            'action' => 'login',
            'login' => $this->config->login(),
            'password' => $this->config->apiToken(),
            'session' => $preSession !== '' ? $preSession : null,
        ], static fn ($value) => $value !== null && $value !== ''));

        $this->parseResponse($loginResponse, 'login');

        $postSession = $this->commonInfoSession($preSession !== '' ? $preSession : $this->extractBreezeSession($loginResponse));
        if ($postSession === '') {
            $postSession = $this->extractBreezeSession($loginResponse);
        }

        if ($postSession === '') {
            throw new RuntimeException('Adobe Connect login succeeded but BREEZESESSION cookie was not returned.');
        }

        Cache::put($cacheKey, $postSession, now()->addMinutes($this->config->sessionTtlMinutes()));

        return $postSession;
    }

    private function commonInfoSession(?string $existingSession = null): string
    {
        $query = ['action' => 'common-info'];
        if ($existingSession !== null && $existingSession !== '') {
            $query['session'] = $existingSession;
        }

        $response = $this->http()->get('/api/xml', $query);
        $xml = $this->parseResponse($response, 'common-info');

        $fromXml = trim((string) ($xml->cookie ?? ''));
        if ($fromXml !== '') {
            return $fromXml;
        }

        return $this->extractBreezeSession($response);
    }

    private function http(): PendingRequest
    {
        $base = $this->config->baseUrl();
        if ($base === '') {
            throw new RuntimeException('Adobe Connect base URL is not configured.');
        }

        return Http::baseUrl($base)
            ->timeout($this->config->httpTimeoutSeconds())
            ->accept('application/xml');
    }

    private function parseResponse(Response $response, string $action): SimpleXMLElement
    {
        if (! $response->successful()) {
            throw new RuntimeException(sprintf(
                'Adobe Connect API HTTP error for action [%s]: %s',
                $action,
                $response->status(),
            ));
        }

        $body = trim($response->body());
        if ($body === '') {
            throw new RuntimeException("Adobe Connect API returned an empty body for action [{$action}].");
        }

        $xml = @simplexml_load_string($body);
        if ($xml === false) {
            throw new RuntimeException("Adobe Connect API returned invalid XML for action [{$action}].");
        }

        $code = (string) ($xml->status['code'] ?? '');
        if ($code !== '' && $code !== 'ok') {
            $subcode = (string) ($xml->status['subcode'] ?? '');
            if ($action !== 'login' && $action !== 'common-info' && $this->isSessionStatusCode($code)) {
                $this->forgetSession();
            }

            $detail = $subcode !== '' ? "{$code}/{$subcode}" : $code;

            throw new RuntimeException("Adobe Connect API action [{$action}] failed with status [{$detail}].");
        }

        return $xml;
    }

    private function extractBreezeSession(Response $response): string
    {
        foreach ($response->cookies() as $cookie) {
            if (strcasecmp($cookie->getName(), 'BREEZESESSION') === 0) {
                return (string) $cookie->getValue();
            }
        }

        return '';
    }

    private function isSessionFailure(RuntimeException $exception): bool
    {
        $message = $exception->getMessage();

        return str_contains($message, 'status [no-access]')
            || str_contains($message, 'status [no-access/')
            || str_contains($message, 'status [expired]')
            || str_contains($message, 'status [expired/]');
    }

    private function isSessionStatusCode(string $code): bool
    {
        return in_array($code, ['no-access', 'expired'], true);
    }
}
