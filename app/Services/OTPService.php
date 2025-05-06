<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class OTPService
{
    public static function generateAndSendOtp($phone): void
    {
        $otp = random_int(100000, 999999);
        Cache::put("otp_{$phone}", $otp, now()->addMinutes(5));

        $message = "Your OTP is: {$otp}";
        (new TwilioService)->sendSms($phone, $message);
    }

    public static function verifyOTP($phone, $otp): bool
    {
        $cacheOtp = Cache::get("otp_{$phone}");

        return (string) $cacheOtp === $otp;
    }
}
