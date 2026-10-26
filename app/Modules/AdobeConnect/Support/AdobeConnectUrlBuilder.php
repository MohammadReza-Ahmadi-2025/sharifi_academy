<?php

namespace App\Modules\AdobeConnect\Support;

final class AdobeConnectUrlBuilder
{
    public function __construct(
        private readonly AdobeConnectConfig $config,
    ) {}

    public function meetingJoinUrl(string $urlPath): string
    {
        $path = '/'.ltrim($urlPath, '/');

        return $this->config->baseUrl().$path;
    }

    /**
     * آدرس پخش/باز کردن ضبط در مرورگر (آرشیو جلسه).
     */
    public function recordingPlaybackUrl(string $urlPath): string
    {
        return $this->meetingJoinUrl($urlPath);
    }

    /**
     * دانلود MP4 (Enhanced AV) طبق مستندات Adobe Connect.
     */
    public function recordingMp4DownloadUrl(string $urlPath, string $fileName): string
    {
        $base = rtrim($this->recordingPlaybackUrl($urlPath), '/');
        $name = rawurlencode($fileName !== '' ? $fileName : 'recording.mp4');

        return $base.'/?download=true&name='.$name;
    }
}
