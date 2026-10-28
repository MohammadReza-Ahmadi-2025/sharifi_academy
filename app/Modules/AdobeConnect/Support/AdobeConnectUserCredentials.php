<?php

namespace App\Modules\AdobeConnect\Support;

use App\Models\User;
use App\Modules\Auth\Support\MobileNormalizer;

final class AdobeConnectUserCredentials
{
    public function __construct(
        public readonly string $login,
        public readonly string $password,
    ) {}

    public static function forUser(User $user): ?self
    {
        $login = MobileNormalizer::normalize($user->mobile);
        if (! MobileNormalizer::isValidIranMobile($login)) {
            return null;
        }

        return new self($login, $login);
    }
}
