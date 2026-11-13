<?php

namespace App\Modules\AdobeConnect\Support;

final class AdobeEnrollmentSyncResult
{
    public function __construct(
        public readonly string $status,
        public readonly string $message,
    ) {}

    public static function skipped(string $message): self
    {
        return new self('skipped', $message);
    }

    public static function linked(string $login): self
    {
        return new self('linked', "Adobe Connect: کاربر {$login} وصل شد و دسترسی اتاق داده شد.");
    }

    public static function created(string $login): self
    {
        return new self('created', "Adobe Connect: کاربر {$login} ساخته شد (نام کاربری و رمز = موبایل).");
    }

    public static function failed(string $login, ?string $reason = null): self
    {
        $detail = filled($reason)
            ? ' دلیل: '.$reason
            : ' دوباره «وصل Adobe» را بزنید و لاگ Adobe Connect را برای دلیل دقیق بررسی کنید.';

        return new self(
            'failed',
            "Adobe Connect: کاربر {$login} ساخته/وصل نشد.{$detail}",
        );
    }

    public static function revoked(string $login): self
    {
        return new self('revoked', "Adobe Connect: دسترسی اتاق برای {$login} برداشته شد.");
    }

    public static function revokeFailed(string $login, ?string $reason = null): self
    {
        $detail = filled($reason) ? ' دلیل: '.$reason : '';

        return new self(
            'revoke_failed',
            "Adobe Connect: برداشتن دسترسی {$login} انجام نشد.{$detail}",
        );
    }

    public function appendToFlash(string $base): string
    {
        if ($this->status === 'skipped' && $this->message === '') {
            return $base;
        }

        if ($this->message === '') {
            return $base;
        }

        return $base.' '.$this->message;
    }
}
