<?php

namespace Tests\Feature\Auth;

use App\Modules\Auth\Contracts\OtpSender;
use App\Modules\Auth\Services\LogOtpSender;
use App\Modules\Auth\Services\SmsIrOtpSender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SmsIrOtpSenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_verify_request_with_otp_parameter(): void
    {
        config([
            'sms.drivers.smsir.base_url' => 'https://api.sms.ir/v1',
            'sms.drivers.smsir.api_key' => 'test-key',
            'sms.templates.login' => 739984,
        ]);

        Http::fake([
            'https://api.sms.ir/v1/send/verify' => Http::response(['status' => 1], 200),
        ]);

        $sender = $this->app->make(SmsIrOtpSender::class);
        $sender->send('09123456789', '654321', 'login');

        Http::assertSent(function ($request) {
            $body = $request->data();

            return $request->url() === 'https://api.sms.ir/v1/send/verify'
                && $request->hasHeader('x-api-key', 'test-key')
                && ($body['mobile'] ?? null) === '09123456789'
                && ($body['templateId'] ?? null) === 739984
                && ($body['parameters'] ?? []) === [
                    ['name' => 'OTP', 'value' => '654321'],
                ];
        });
    }

    public function test_unit_tests_use_log_sender_not_sms(): void
    {
        $this->assertInstanceOf(
            LogOtpSender::class,
            $this->app->make(OtpSender::class),
        );
    }
}
