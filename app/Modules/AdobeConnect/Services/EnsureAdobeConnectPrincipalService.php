<?php

namespace App\Modules\AdobeConnect\Services;

use App\Models\User;
use App\Modules\AdobeConnect\Support\AdobeConnectUserCredentials;
use Illuminate\Support\Facades\Log;
use Throwable;

class EnsureAdobeConnectPrincipalService
{
    public function __construct(
        private readonly AdobeConnectApi $api,
    ) {}

    /**
     * @return array{0: ?string, 1: bool} principal id, was newly created via API
     */
    public function ensure(User $user, AdobeConnectUserCredentials $credentials): array
    {
        $firstName = trim((string) ($user->name ?? ''));
        $lastName = trim((string) ($user->family ?? ''));
        if ($firstName === '') {
            $firstName = 'Student';
        }
        if ($lastName === '') {
            $lastName = 'User';
        }

        $existingId = $this->api->findPrincipalIdByLogin($credentials->login);
        if ($existingId !== null) {
            $this->api->updateUserProfile(
                $existingId,
                $firstName,
                $lastName,
                $credentials->password,
            );
            $this->persistPrincipal($user, $existingId);

            return [$existingId, false];
        }

        if (filled($user->adobe_connect_principal_id)) {
            $storedId = (string) $user->adobe_connect_principal_id;
            try {
                $this->api->updateUserProfile(
                    $storedId,
                    $firstName,
                    $lastName,
                    $credentials->password,
                );
                $this->persistPrincipal($user, $storedId);

                return [$storedId, false];
            } catch (Throwable) {
                // fall through to create / re-find
            }
        }

        try {
            $registered = $this->api->registerUser(
                $credentials->login,
                $credentials->password,
                $firstName,
                $lastName,
            );
            $this->persistPrincipal($user, $registered['principal_id']);

            return [$registered['principal_id'], true];
        } catch (Throwable $exception) {
            Log::warning('Adobe Connect user auto-registration failed', [
                'user_id' => $user->id,
                'login' => $credentials->login,
                'message' => $exception->getMessage(),
            ]);

            $retryFind = $this->api->findPrincipalIdByLogin($credentials->login);
            if ($retryFind !== null) {
                $this->api->updateUserProfile(
                    $retryFind,
                    $firstName,
                    $lastName,
                    $credentials->password,
                );
                $this->persistPrincipal($user, $retryFind);

                return [$retryFind, false];
            }

            return [null, false];
        }
    }

    private function persistPrincipal(User $user, string $principalId): void
    {
        $user->forceFill([
            'adobe_connect_principal_id' => $principalId,
            'adobe_connect_synced_at' => now(),
        ])->save();
    }
}
