<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Auth\Http\Requests\PasswordLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PasswordLoginController extends Controller
{
    public function __invoke(PasswordLoginRequest $request): RedirectResponse
    {
        $user = User::query()->where('mobile', $request->mobile())->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'شماره موبایل یا رمز عبور اشتباه است.',
            ]);
        }

        if ((int) $user->status !== 1) {
            throw ValidationException::withMessages([
                'phone' => 'حساب کاربری غیرفعال است.',
            ]);
        }

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        Auth::login($user, true);

        return redirect()->intended(config('fortify.home', '/'));
    }
}
