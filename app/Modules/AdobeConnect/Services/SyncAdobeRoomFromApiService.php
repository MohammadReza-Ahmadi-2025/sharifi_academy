<?php

namespace App\Modules\AdobeConnect\Services;

use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\AdobeConnect\Support\AdobeConnectConfig;
use App\Modules\AdobeConnect\Support\AdobeConnectUrlBuilder;

class SyncAdobeRoomFromApiService
{
    public function __construct(
        private readonly AdobeConnectConfig $config,
        private readonly AdobeConnectApi $api,
        private readonly AdobeConnectUrlBuilder $urls,
    ) {}

    public function sync(AdobeRoom $room): AdobeRoom
    {
        if (! $this->config->isApiConfigured()) {
            throw new \RuntimeException('برای سینک اتاق، ADOBE_CONNECT_BASE_URL، ADOBE_CONNECT_LOGIN و ADOBE_CONNECT_API_TOKEN را در .env تنظیم کنید.');
        }

        $info = $this->api->scoInfo((string) $room->sco_id);
        $urlPath = trim($info['url_path']);
        if ($urlPath === '') {
            throw new \RuntimeException('Adobe Connect برای این sco-id مسیر url-path برنگرداند.');
        }

        $room->fill([
            'url_path' => $urlPath,
            'meeting_url' => $this->urls->meetingJoinUrl($urlPath),
            'last_synced_at' => now(),
        ]);

        $room->save();

        return $room->refresh();
    }
}
