<?php

namespace App\Modules\AdobeConnect\Support;

use App\Modules\Settings\Services\SiteSettingsService;

final class AdobeConnectConfig
{
    public function baseUrl(): string
    {
        $fromEnv = trim((string) config('adobe_connect.base_url', ''));
        if ($fromEnv !== '') {
            return rtrim($fromEnv, '/');
        }

        $fromSettings = trim((string) app(SiteSettingsService::class)->get('adobe_connect_base_url', ''));

        return rtrim($fromSettings, '/');
    }

    public function login(): string
    {
        return trim((string) config('adobe_connect.login', ''));
    }

    public function apiToken(): string
    {
        return trim((string) config('adobe_connect.api_token', ''));
    }

    public function isApiConfigured(): bool
    {
        return $this->baseUrl() !== ''
            && $this->login() !== ''
            && $this->apiToken() !== '';
    }

    public function sessionCacheKey(): string
    {
        return (string) config('adobe_connect.session_cache_key', 'adobe_connect.breeze_session');
    }

    public function sessionTtlMinutes(): int
    {
        return max(5, (int) config('adobe_connect.session_ttl_minutes', 50));
    }

    public function httpTimeoutSeconds(): int
    {
        return max(5, (int) config('adobe_connect.http_timeout_seconds', 30));
    }

    public function meetingsFolderId(): ?string
    {
        $value = trim((string) config('adobe_connect.meetings_folder_id', ''));

        return $value !== '' ? $value : null;
    }

    public function autoProvisionMeetings(): bool
    {
        return (bool) config('adobe_connect.auto_provision_meetings', true);
    }

    public function autoSyncEnabled(): bool
    {
        return (bool) config('adobe_connect.auto_sync_enabled', true);
    }

    public function autoSyncWithinHours(): int
    {
        return max(1, (int) config('adobe_connect.auto_sync_within_hours', 48));
    }

    /**
     * @return list<string>
     */
    public function usersFolderIds(): array
    {
        $ids = config('adobe_connect.users_folder_ids', []);

        return is_array($ids) ? array_values(array_filter($ids, fn ($id) => is_string($id) && $id !== '')) : [];
    }
}
