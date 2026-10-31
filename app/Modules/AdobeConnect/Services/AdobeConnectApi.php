<?php

namespace App\Modules\AdobeConnect\Services;

use App\Modules\AdobeConnect\Support\AdobeConnectConfig;

class AdobeConnectApi
{
    public function __construct(
        private readonly AdobeConnectXmlClient $client,
        private readonly AdobeConnectConfig $config,
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

    /**
     * @return array{sco_id: string, name: string, url_path: string}
     */
    public function createMeeting(
        string $name,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        ?string $urlPath = null,
    ): array {
        $folderId = $this->resolveMeetingsFolderId();

        $query = [
            'type' => 'meeting',
            'name' => $name,
            'folder-id' => $folderId,
            'date-begin' => $begin->format('Y-m-d\TH:i'),
            'date-end' => $end->format('Y-m-d\TH:i'),
            'lang' => 'en',
        ];

        if ($urlPath !== null && $urlPath !== '') {
            $query['url-path'] = ltrim($urlPath, '/');
        }

        $xml = $this->client->get('sco-update', $query);
        $sco = $xml->sco ?? null;
        if ($sco === null) {
            throw new \RuntimeException('Adobe Connect sco-update did not return a meeting sco.');
        }

        $scoId = (string) ($sco['sco-id'] ?? '');
        $path = (string) ($sco->{'url-path'} ?? '');
        if ($scoId === '' || $path === '') {
            throw new \RuntimeException('Adobe Connect meeting was created but sco-id or url-path is missing.');
        }

        return [
            'sco_id' => $scoId,
            'name' => (string) ($sco->name ?? $name),
            'url_path' => $path,
        ];
    }

    public function resolveMeetingsFolderId(): string
    {
        $override = $this->config->meetingsFolderId();
        if ($override !== null) {
            return $override;
        }

        $xml = $this->client->get('sco-shortcuts');

        if (! isset($xml->shortcuts->sco)) {
            throw new \RuntimeException('Adobe Connect sco-shortcuts returned no folders.');
        }

        foreach ($xml->shortcuts->sco as $sco) {
            $type = (string) ($sco['type'] ?? '');
            if (in_array($type, ['user-meetings', 'my-meetings'], true)) {
                $folderId = (string) ($sco['sco-id'] ?? '');
                if ($folderId !== '') {
                    return $folderId;
                }
            }
        }

        throw new \RuntimeException('Could not find user-meetings folder in Adobe Connect sco-shortcuts.');
    }

    public function findPrincipalIdByLogin(string $login): ?string
    {
        $login = trim($login);
        if ($login === '') {
            return null;
        }

        $xml = $this->client->get('principal-list', ['filter-login' => $login]);
        if (! isset($xml->{'principal-list'}->principal)) {
            return null;
        }

        $principal = $xml->{'principal-list'}->principal;
        $id = (string) ($principal['principal-id'] ?? '');

        return $id !== '' ? $id : null;
    }

    /**
     * @return array{principal_id: string, login: string}
     */
    public function registerUser(
        string $login,
        string $password,
        string $firstName,
        string $lastName,
        ?string $email = null,
    ): array {
        $existing = $this->findPrincipalIdByLogin($login);
        if ($existing !== null) {
            $this->updateUserPassword($existing, $password);

            return ['principal_id' => $existing, 'login' => $login];
        }

        $email ??= $login.'@students.academy-sharifi.ir';

        $folderIds = $this->config->usersFolderIds();
        if ($folderIds === []) {
            $folderIds = $this->discoverUsersFolderIds();
        }

        $lastException = null;
        foreach ($folderIds as $folderId) {
            try {
                $xml = $this->client->get('principal-update', [
                    'first-name' => $firstName !== '' ? $firstName : 'Student',
                    'last-name' => $lastName !== '' ? $lastName : 'User',
                    'login' => $login,
                    'password' => $password,
                    'confirm-password' => $password,
                    'type' => 'user',
                    'send-email' => 'false',
                    'email' => $email,
                    'folder-id' => $folderId,
                ]);

                $principalId = (string) ($xml->principal['principal-id'] ?? '');
                if ($principalId === '') {
                    continue;
                }

                return ['principal_id' => $principalId, 'login' => $login];
            } catch (\Throwable $exception) {
                $lastException = $exception;
            }
        }

        throw $lastException ?? new \RuntimeException('Adobe Connect user registration failed.');
    }

    public function updateUserPassword(string $principalId, string $password): void
    {
        $this->client->get('principal-update', [
            'principal-id' => $principalId,
            'password' => $password,
            'confirm-password' => $password,
        ]);
    }

    public function updateUserProfile(
        string $principalId,
        string $firstName,
        string $lastName,
        string $password,
    ): void {
        $this->client->get('principal-update', [
            'principal-id' => $principalId,
            'first-name' => $firstName,
            'last-name' => $lastName,
            'password' => $password,
            'confirm-password' => $password,
        ]);
    }

    /**
     * گزارش حضور شرکت‌کنندگان یک اتاق جلسه (report-meeting-attendance).
     *
     * @param  array{date_gte?: string|null, date_lte?: string|null}  $filters
     * @return list<array{
     *     principal_id: string,
     *     login: string,
     *     participant_name: string,
     *     date_created: string|null,
     *     date_end: string|null
     * }>
     */
    public function meetingAttendance(string $meetingScoId, array $filters = []): array
    {
        $query = ['sco-id' => $meetingScoId];

        $dateGte = trim((string) ($filters['date_gte'] ?? ''));
        $dateLte = trim((string) ($filters['date_lte'] ?? ''));
        if ($dateGte !== '') {
            $query['filter-gte-date-created'] = $dateGte;
        }
        if ($dateLte !== '') {
            $query['filter-lte-date-created'] = $dateLte;
        }

        $xml = $this->client->get('report-meeting-attendance', $query);

        if (! isset($xml->{'report-meeting-attendance'}->row)) {
            return [];
        }

        $rows = [];
        foreach ($xml->{'report-meeting-attendance'}->row as $row) {
            $principalId = (string) ($row['principal-id'] ?? '');
            $login = trim((string) ($row->login ?? ''));
            if ($principalId === '' && $login === '') {
                continue;
            }

            $rows[] = [
                'principal_id' => $principalId,
                'login' => $login,
                'participant_name' => trim((string) ($row->{'participant-name'} ?? '')),
                'date_created' => isset($row->{'date-created'}) ? (string) $row->{'date-created'} : null,
                'date_end' => isset($row->{'date-end'}) ? (string) $row->{'date-end'} : null,
            ];
        }

        return $rows;
    }

    public function grantMeetingViewAccess(string $meetingScoId, string $principalId): void
    {
        $this->grantMeetingPermission($meetingScoId, $principalId, 'view');
    }

    public function grantMeetingHostAccess(string $meetingScoId, string $principalId): void
    {
        $this->grantMeetingPermission($meetingScoId, $principalId, 'host');
    }

    /**
     * @param  'view'|'host'|'mini-host'  $permission
     */
    public function grantMeetingPermission(string $meetingScoId, string $principalId, string $permission = 'view'): void
    {
        $this->client->get('permissions-update', [
            'acl-id' => $meetingScoId,
            'principal-id' => $principalId,
            'permission-id' => $permission,
        ]);
    }

    /**
     * @return list<string>
     */
    private function discoverUsersFolderIds(): array
    {
        try {
            $xml = $this->client->get('sco-shortcuts');
        } catch (\Throwable) {
            return ['11001', '11000', '7'];
        }

        $ids = [];
        if (isset($xml->shortcuts->sco)) {
            foreach ($xml->shortcuts->sco as $sco) {
                $type = (string) ($sco['type'] ?? '');
                if (preg_match('/user|content|account|custom/i', $type) === 1) {
                    $id = (string) ($sco['sco-id'] ?? '');
                    if ($id !== '') {
                        $ids[] = $id;
                    }
                }
            }
        }

        $ids = array_values(array_unique($ids));

        return $ids !== [] ? $ids : ['11001', '11000', '7'];
    }
}
