<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Http\Requests\OtpRequestRequest;
use App\Modules\Auth\Services\MobileOtpService;
use Illuminate\Http\RedirectResponse;

class OtpRequestController extends Controller
{
    public function __invoke(OtpRequestRequest $request, MobileOtpService $otpService): RedirectResponse
    {
        $otpService->requestCode($request->mobile(), $request);

        return back()->with('success', 'کد یکبار مصرف ارسال شد.');
    }
}
