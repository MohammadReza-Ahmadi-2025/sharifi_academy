<?php

namespace App\Modules\AdobeConnect\Services;

use App\Modules\AdobeConnect\Support\AdobeConnectConfig;
use App\Modules\AdobeConnect\Support\AdobeConnectUrlBuilder;
use Illuminate\Validation\ValidationException;
use Throwable;

class AdobeRoomPayloadFactory
{
    public function __construct(
        private readonly AdobeConnectConfig $config,
        private readonly AdobeConnectApi $api,
        private readonly AdobeConnectUrlBuilder $urls,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function resolveForCreate(array $data): array
    {
        $urlPath = trim((string) ($data['url_path'] ?? ''));
        $meetingUrl = trim((string) ($data['meeting_url'] ?? ''));
        $scoId = trim((string) ($data['sco_id'] ?? ''));

        if ($urlPath === '') {
            if (! $this->config->isApiConfigured()) {
                throw ValidationException::withMessages([
                    'url_path' => 'مسیر اتاق خالی است. یا url-path را دستی وارد کنید یا ADOBE_CONNECT_* را در .env تنظیم کنید.',
                ]);
            }

            try {
                $info = $this->api->scoInfo($scoId);
            } catch (Throwable $exception) {
                report($exception);

                throw ValidationException::withMessages([
                    'sco_id' => 'دریافت اطلاعات اتاق از Adobe Connect ناموفق بود؛ اتصال، حساب سرویس و sco-id را بررسی کنید.',
                ]);
            }
            $urlPath = trim($info['url_path']);
            if ($urlPath === '') {
                throw ValidationException::withMessages([
                    'sco_id' => 'Adobe Connect برای این sco-id مسیر url-path برنگرداند.',
                ]);
            }
        }

        if ($meetingUrl === '') {
            $meetingUrl = $this->urls->meetingJoinUrl($urlPath);
        }

        $data['url_path'] = $urlPath;
        $data['meeting_url'] = $meetingUrl;
        $data['last_synced_at'] = now();

        return $data;
    }
}
