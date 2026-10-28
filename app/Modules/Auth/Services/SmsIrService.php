<?php

namespace App\Modules\Auth\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SmsIrService
{
    protected string $baseUrl;

    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = (string) config('sms.drivers.smsir.base_url');
        $this->apiKey = (string) config('sms.drivers.smsir.api_key');
    }

    /**
     * @param  array<string, string|int>  $params  Template placeholders (e.g. OTP for #OTP#)
     * @return array<string, mixed>
     */
    public function sendVerify(string $templateKey, string $mobile, array $params = []): array
    {
        $templateId = config("sms.templates.{$templateKey}");

        if (! $templateId) {
            throw new RuntimeException("SMS template [{$templateKey}] not defined.");
        }

        if ($this->apiKey === '') {
            throw new RuntimeException('SMS API key is not configured.');
        }

        $response = Http::timeout(5)
            ->retry(3, 200)
            ->withHeaders([
                'x-api-key' => $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post($this->baseUrl.'/send/verify', [
                'mobile' => $mobile,
                'templateId' => (int) $templateId,
                'parameters' => $this->formatParameters($params),
            ]);

        $data = $response->json();

        if (! $response->successful()) {
            Log::error('SMS HTTP failure', [
                'mobile' => $mobile,
                'template' => $templateKey,
                'status' => $response->status(),
            ]);

            throw new RuntimeException('SMS provider HTTP error.');
        }

        if (isset($data['status']) && (int) $data['status'] !== 1) {
            Log::error('SMS provider rejected message', [
                'mobile' => $mobile,
                'template' => $templateKey,
                'response' => $data,
            ]);

            throw new RuntimeException('SMS provider rejected request.');
        }

        return is_array($data) ? $data : [];
    }

    /**
     * @param  array<string, string|int>  $parameters
     * @return list<array{name: string, value: string|int}>
     */
    protected function formatParameters(array $parameters): array
    {
        return collect($parameters)
            ->map(fn ($value, $key) => [
                'name' => (string) $key,
                'value' => $value,
            ])
            ->values()
            ->all();
    }
}
