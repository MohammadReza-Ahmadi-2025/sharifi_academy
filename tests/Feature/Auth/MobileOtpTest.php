<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Modules\Auth\Contracts\OtpCodeGenerator;
use App\Modules\Auth\Enums\OtpStatus;
use App\Modules\Auth\Models\MobileOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
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
        $this->assertTrue(User::where('mobile', '09121111111')->first()?->hasRole('student') ?? false);
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
}
