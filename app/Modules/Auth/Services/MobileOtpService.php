<?php

namespace App\Modules\Auth\Services;

use App\Models\User;
use App\Modules\Auth\Contracts\OtpCodeGenerator;
use App\Modules\Auth\Contracts\OtpSender;
use App\Modules\Auth\Enums\OtpStatus;
use App\Modules\Auth\Models\MobileOtp;
use App\Modules\Auth\Support\MobileNormalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MobileOtpService
{
    public function __construct(
        private readonly OtpCodeGenerator $codeGenerator,
        private readonly OtpSender $otpSender,
    ) {}

    /**
     * @throws ValidationException
     */
    public function requestCode(string $rawMobile, Request $request, ?string $type = null): void
    {
        $mobile = MobileNormalizer::normalize($rawMobile);
        $type = $type ?? config('otp.default_type', 'login');

        $this->assertValidMobile($mobile);
        $this->assertRateLimits($mobile, $request->ip());
        $this->assertResendCooldown($mobile, $type);

        $codeLength = (int) config('otp.code_length', 6);
        $code = $this->codeGenerator->generate($codeLength);
        $expiresMinutes = (int) config('otp.expires_minutes', 5);
        $maxAttempts = (int) config('otp.max_attempts', 5);

        DB::transaction(function () use ($mobile, $type, $code, $request, $expiresMinutes, $maxAttempts) {
            MobileOtp::query()
                ->where('mobile', $mobile)
                ->where('type', $type)
                ->where('status', OtpStatus::Pending)
                ->update(['status' => OtpStatus::Expired]);

            MobileOtp::query()->create([
                'mobile' => $mobile,
                'type' => $type,
                'code_hash' => Hash::make($code),
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 512, ''),
                'max_attempts' => $maxAttempts,
                'status' => OtpStatus::Pending,
                'expires_at' => now()->addMinutes($expiresMinutes),
            ]);

            $this->otpSender->send($mobile, $code, $type);
        });

        $this->hitRateLimiters($mobile, $request->ip());
    }

    /**
     * @throws ValidationException
     */
    public function verifyAndLogin(string $rawMobile, string $rawCode, Request $request, ?string $type = null): User
    {
        $mobile = MobileNormalizer::normalize($rawMobile);
        $code = MobileNormalizer::normalize($rawCode);
        $type = $type ?? config('otp.default_type', 'login');

        $this->assertValidMobile($mobile);

        if (! preg_match('/^\d{5,6}$/', $code)) {
            throw ValidationException::withMessages([
                'code' => 'کد ۵ یا ۶ رقمی را وارد کنید.',
            ]);
        }

        $otp = MobileOtp::query()
            ->where('mobile', $mobile)
            ->where('type', $type)
            ->where('status', OtpStatus::Pending)
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $otp) {
            throw ValidationException::withMessages([
                'code' => 'کد منقضی شده یا نامعتبر است. دوباره درخواست دهید.',
            ]);
        }

        if (! $otp->hasAttemptsRemaining()) {
            $otp->update(['status' => OtpStatus::Blocked]);

            throw ValidationException::withMessages([
                'code' => 'تعداد تلاش‌ها بیش از حد مجاز است. کد جدید بگیرید.',
            ]);
        }

        if (! Hash::check($code, $otp->code_hash)) {
            $otp->increment('attempts');
            $otp->refresh();

            if ($otp->attempts >= $otp->max_attempts) {
                $otp->update(['status' => OtpStatus::Failed]);
            }

            throw ValidationException::withMessages([
                'code' => 'کد وارد شده اشتباه است.',
            ]);
        }

        $otp->update([
            'status' => OtpStatus::Verified,
            'verified_at' => now(),
        ]);

        $user = $this->findOrCreateUser($mobile);

        $user->forceFill([
            'mobile_verified_at' => now(),
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        Auth::login($user, true);

        return $user;
    }

    protected function findOrCreateUser(string $mobile): User
    {
        $existing = User::query()->where('mobile', $mobile)->first();

        if ($existing) {
            return $existing;
        }

        $user = User::query()->create([
            'mobile' => $mobile,
            'password' => Str::password(32),
            'status' => 1,
            'mobile_verified_at' => now(),
        ]);

        $role = config('otp.new_user_role', 'student');
        if ($role) {
            $user->assignRole($role);
        }

        return $user;
    }

    /**
     * @throws ValidationException
     */
    protected function assertValidMobile(string $mobile): void
    {
        if (! MobileNormalizer::isValidIranMobile($mobile)) {
            throw ValidationException::withMessages([
                'phone' => 'شماره موبایل معتبر نیست (مثال: 09123456789).',
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    protected function assertRateLimits(string $mobile, ?string $ip): void
    {
        $mobileKey = 'otp:mobile:'.$mobile;
        $mobileMax = (int) config('otp.rate_limit.mobile.max_attempts', 5);
        $mobileDecay = (int) config('otp.rate_limit.mobile.decay_minutes', 60) * 60;

        if (RateLimiter::tooManyAttempts($mobileKey, $mobileMax)) {
            $seconds = RateLimiter::availableIn($mobileKey);

            throw ValidationException::withMessages([
                'phone' => 'درخواست‌های زیاد برای این شماره. '.ceil($seconds / 60).' دقیقه دیگر تلاش کنید.',
            ]);
        }

        if ($ip === null) {
            return;
        }

        $ipKey = 'otp:ip:'.$ip;
        $ipMax = (int) config('otp.rate_limit.ip.max_attempts', 15);
        $ipDecay = (int) config('otp.rate_limit.ip.decay_minutes', 60) * 60;

        if (RateLimiter::tooManyAttempts($ipKey, $ipMax)) {
            $seconds = RateLimiter::availableIn($ipKey);

            throw ValidationException::withMessages([
                'phone' => 'درخواست‌های زیاد از این IP. '.ceil($seconds / 60).' دقیقه دیگر تلاش کنید.',
            ]);
        }
    }

    protected function hitRateLimiters(string $mobile, ?string $ip): void
    {
        $mobileDecay = (int) config('otp.rate_limit.mobile.decay_minutes', 60) * 60;
        RateLimiter::hit('otp:mobile:'.$mobile, $mobileDecay);

        if ($ip !== null) {
            $ipDecay = (int) config('otp.rate_limit.ip.decay_minutes', 60) * 60;
            RateLimiter::hit('otp:ip:'.$ip, $ipDecay);
        }
    }

    /**
     * @throws ValidationException
     */
    protected function assertResendCooldown(string $mobile, string $type): void
    {
        $cooldown = (int) config('otp.resend_cooldown_seconds', 120);

        $latest = MobileOtp::query()
            ->where('mobile', $mobile)
            ->where('type', $type)
            ->latest('id')
            ->first();

        if (! $latest) {
            return;
        }

        if ($latest->created_at === null) {
            return;
        }

        $nextAllowedAt = $latest->created_at->copy()->addSeconds($cooldown);

        if (now()->lt($nextAllowedAt)) {
            $remaining = (int) now()->diffInSeconds($nextAllowedAt);

            throw ValidationException::withMessages([
                'phone' => "ارسال مجدد تا {$remaining} ثانیه دیگر ممکن است.",
            ]);
        }
    }
}
