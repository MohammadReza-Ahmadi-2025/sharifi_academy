<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Modules\Auth\Contracts\OtpCodeGenerator;
use App\Modules\Auth\Enums\OtpStatus;
use App\Modules\Auth\Models\MobileOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class MobileOtpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(OtpCodeGenerator::class, new class implements OtpCodeGenerator
        {
            public function generate(int $length): string
            {
                return '123456';
            }
        });
    }

    public function test_guest_can_request_otp(): void
    {
        $this->post('/auth/otp/request', ['phone' => '09123456789'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('mobile_otps', [
            'mobile' => '09123456789',
            'type' => 'login',
            'status' => OtpStatus::Pending->value,
        ]);
    }

    public function test_verify_creates_user_and_logs_in(): void
    {
        $this->post('/auth/otp/request', ['phone' => '09121111111']);

        $this->post('/auth/otp/verify', [
            'phone' => '09121111111',
            'code' => '123456',
        ])->assertRedirect('/');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['mobile' => '09121111111']);
        $user = User::where('mobile', '09121111111')->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->hasRole('student'));
    }

    public function test_verify_existing_user_logs_in_without_duplicate(): void
    {
        $user = User::factory()->create(['mobile' => '09122222222']);

        $this->post('/auth/otp/request', ['phone' => '09122222222']);
        $this->post('/auth/otp/verify', [
            'phone' => '09122222222',
            'code' => '123456',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, User::query()->where('mobile', '09122222222')->count());
    }

    public function test_wrong_code_increments_attempts(): void
    {
        $this->post('/auth/otp/request', ['phone' => '09123333333']);

        $this->post('/auth/otp/verify', [
            'phone' => '09123333333',
            'code' => '000000',
        ])->assertSessionHasErrors('code');

        $otp = MobileOtp::query()->where('mobile', '09123333333')->first();
        $this->assertSame(1, $otp?->attempts);
        $this->assertFalse(Auth::check());
    }

    public function test_resend_cooldown_blocks_rapid_requests(): void
    {
        config(['otp.resend_cooldown_seconds' => 120]);

        $this->post('/auth/otp/request', ['phone' => '09124444444'])->assertSessionHasNoErrors();
        $this->post('/auth/otp/request', ['phone' => '09124444444'])->assertSessionHasErrors('phone');
    }

    public function test_expired_code_cannot_be_verified(): void
    {
        config(['otp.expires_minutes' => 2]);

        $this->post('/auth/otp/request', ['phone' => '09125555555'])->assertSessionHasNoErrors();

        $this->travel(121)->seconds();

        $this->post('/auth/otp/verify', [
            'phone' => '09125555555',
            'code' => '123456',
        ])->assertSessionHasErrors('code');

        $this->assertFalse(Auth::check());
    }

    public function test_max_wrong_attempts_blocks_otp(): void
    {
        config(['otp.max_attempts' => 3]);

        $this->post('/auth/otp/request', ['phone' => '09126666666']);

        for ($i = 0; $i < 3; $i++) {
            $this->post('/auth/otp/verify', [
                'phone' => '09126666666',
                'code' => '000000',
            ])->assertSessionHasErrors('code');
        }

        $otp = MobileOtp::query()->where('mobile', '09126666666')->first();
        $this->assertSame(OtpStatus::Blocked, $otp?->status);
        $this->assertSame(3, $otp?->attempts);

        $this->post('/auth/otp/verify', [
            'phone' => '09126666666',
            'code' => '123456',
        ])->assertSessionHasErrors('code');

        $this->assertFalse(Auth::check());
    }

    public function test_ip_rate_limit_blocks_excessive_sms_requests(): void
    {
        RateLimiter::clear('otp:request-ip:127.0.0.1');

        config([
            'otp.rate_limit.ip.max_attempts' => 3,
            'otp.rate_limit.ip.decay_minutes' => 120,
            'otp.resend_cooldown_seconds' => 0,
        ]);

        for ($i = 0; $i < 3; $i++) {
            $mobile = '0912'.str_pad((string) (7000000 + $i), 7, '0', STR_PAD_LEFT);
            $this->post('/auth/otp/request', ['phone' => $mobile])->assertSessionHasNoErrors();
        }

        $this->post('/auth/otp/request', ['phone' => '09127777777'])
            ->assertSessionHasErrors('phone');
    }
}
