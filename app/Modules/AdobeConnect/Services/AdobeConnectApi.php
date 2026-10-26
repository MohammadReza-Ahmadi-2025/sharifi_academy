<?php

namespace App\Modules\AdobeConnect\Services;

class AdobeConnectApi
{
    public function __construct(
        private readonly AdobeConnectXmlClient $client,
    ) {}

    /**
     * @return array{
     *     sco_id: string,
     *     name: string,
     *     url_path: string,
     *     type: string,
     *     icon: string,
     *     folder_id: string|null
     * }
     */
    public function scoInfo(string $scoId): array
    {
        $xml = $this->client->get('sco-info', ['sco-id' => $scoId]);
        $sco = $xml->sco ?? null;
        if ($sco === null) {
            throw new \RuntimeException("Adobe Connect sco-info returned no sco for id [{$scoId}].");
        }

        return [
            'sco_id' => (string) ($sco['sco-id'] ?? $scoId),
            'name' => (string) ($sco->name ?? ''),
            'url_path' => (string) ($sco->{'url-path'} ?? ''),
            'type' => (string) ($sco['type'] ?? ''),
            'icon' => (string) ($sco['icon'] ?? ''),
            'folder_id' => isset($sco['folder-id']) ? (string) $sco['folder-id'] : null,
        ];
    }

    /**
     * ضبط‌های (archive) داخل یک meeting.
     *
     * @return list<array{
     *     sco_id: string,
     *     name: string,
     *     url_path: string,
     *     duration: string,
     *     date_begin: string|null,
     *     date_end: string|null,
     *     enhanced_av: bool
     * }>
     */
    public function meetingRecordingArchives(string $meetingScoId): array
    {
        $xml = $this->client->get('sco-contents', [
            'sco-id' => $meetingScoId,
            'filter-icon' => 'archive',
        ]);

        if (! isset($xml->scos->sco)) {
            return [];
        }

        $items = [];
        foreach ($xml->scos->sco as $sco) {
            $icon = (string) ($sco['icon'] ?? '');
            if ($icon !== '' && $icon !== 'archive') {
                continue;
            }

            $scoId = (string) ($sco['sco-id'] ?? '');
            if ($scoId === '') {
                continue;
            }

            $items[] = [
                'sco_id' => $scoId,
                'name' => (string) ($sco->name ?? 'ضبط جلسه'),
                'url_path' => (string) ($sco->{'url-path'} ?? ''),
                'duration' => (string) ($sco->duration ?? ''),
                'date_begin' => isset($sco->{'date-begin'}) ? (string) $sco->{'date-begin'} : null,
                'date_end' => isset($sco->{'date-end'}) ? (string) $sco->{'date-end'} : null,
                'enhanced_av' => false,
            ];
        }

        return $items;
    }

    public function recordingUsesEnhancedAv(string $archiveScoId): bool
    {
        try {
            $xml = $this->client->get('sco-info', ['sco-id' => $archiveScoId]);
        } catch (\Throwable) {
            return false;
        }

        $json = json_encode($xml);

        return is_string($json)
            && str_contains($json, 'enhanced-av')
            && str_contains($json, 'true');
    }
}
