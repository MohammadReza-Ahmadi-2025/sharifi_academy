<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Http\Requests\OtpVerifyRequest;
use App\Modules\Auth\Services\MobileOtpService;
use Illuminate\Http\RedirectResponse;

class OtpVerifyController extends Controller
{
    public function __invoke(OtpVerifyRequest $request, MobileOtpService $otpService): RedirectResponse
    {
        $otpService->verifyAndLogin(
            $request->mobile(),
            $request->code(),
            $request,
        );

        return redirect()->intended(config('fortify.home', '/'));
    }
}
