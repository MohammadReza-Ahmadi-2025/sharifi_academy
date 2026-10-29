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

    public static function failed(string $login): self
    {
        return new self(
            'failed',
            "Adobe Connect: کاربر {$login} در سرور ساخته نشد؛ در Adobe Connect Central با login/pass همان موبایل بسازید، سپس ثبت‌نام را دوباره تأیید کنید.",
        );
    }

    public function appendToFlash(string $base): string
    {
        if ($this->status === 'skipped') {
            return $base;
        }

        return $base.' '.$this->message;
    }
}
