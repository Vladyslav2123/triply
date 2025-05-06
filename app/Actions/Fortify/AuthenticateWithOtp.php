<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Services\OTPService;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\FailedTwoFactorLoginResponse;
use Laravel\Fortify\Contracts\LoginResponse;

class AuthenticateWithOtp
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'phone' => 'required|exists:users,phone',
            'otp' => 'required|numeric',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (! $user || ! OTPService::verifyOtp($user->phone, $request->otp)) {
            return app(FailedTwoFactorLoginResponse::class);
        }

        auth()->login($user);

        return app(LoginResponse::class);
    }
}
