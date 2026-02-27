<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class OtpService
{
    private const OTP_LENGTH = 6;
    private const OTP_EXPIRY = 600; // 10 minutes
    private const OTP_ATTEMPTS = 3;
    private const OTP_RESEND_DELAY = 60; // 1 minute

    /**
     * Generate and send OTP to phone number
     */
    public static function sendOtp($phoneNumber)
    {
        $phone = self::normalizePhone($phoneNumber);

        // Check if user can request OTP (rate limiting)
        $resendKey = "otp_resend:{$phone}";
        if (Cache::has($resendKey)) {
            return [
                'success' => false,
                'message' => 'Please wait before requesting another OTP',
                'retry_after' => Cache::get($resendKey),
            ];
        }

        // Generate OTP
        $otp = str_pad(random_int(0, 999999), self::OTP_LENGTH, '0', STR_PAD_LEFT);

        // Store OTP in cache with expiry
        $otpKey = "otp:{$phone}";
        Cache::put($otpKey, [
            'code' => $otp,
            'attempts_left' => self::OTP_ATTEMPTS,
            'created_at' => now(),
        ], self::OTP_EXPIRY);

        // Set resend delay
        Cache::put($resendKey, now()->addSeconds(self::OTP_RESEND_DELAY), self::OTP_RESEND_DELAY);

        // Send SMS (implement with Twilio/AWS SNS/Firebase)
        self::sendSms($phone, $otp);

        return [
            'success' => true,
            'message' => 'OTP sent successfully',
            'expires_in' => self::OTP_EXPIRY,
        ];
    }

    /**
     * Verify OTP code
     */
    public static function verifyOtp($phoneNumber, $otpCode)
    {
        $phone = self::normalizePhone($phoneNumber);
        $otpKey = "otp:{$phone}";

        // Get OTP data
        $otpData = Cache::get($otpKey);

        if (!$otpData) {
            return [
                'success' => false,
                'message' => 'OTP expired. Please request a new one.',
            ];
        }

        // Check attempts
        if ($otpData['attempts_left'] <= 0) {
            Cache::forget($otpKey);
            return [
                'success' => false,
                'message' => 'Too many attempts. Please request a new OTP.',
            ];
        }

        // Verify code
        if ($otpData['code'] !== $otpCode) {
            $otpData['attempts_left']--;
            Cache::put($otpKey, $otpData, self::OTP_EXPIRY);

            return [
                'success' => false,
                'message' => 'Invalid OTP. Please try again.',
                'attempts_left' => $otpData['attempts_left'],
            ];
        }

        // OTP verified - clear it
        Cache::forget($otpKey);
        Cache::forget("otp_resend:{$phone}");

        return [
            'success' => true,
            'message' => 'OTP verified successfully',
            'phone' => $phone,
        ];
    }

    /**
     * Send SMS with OTP via GreenWeb API
     */
    private static function sendSms($phoneNumber, $otp)
    {
        try {
            $token = config('services.otp.api_key');
            
            if (!$token) {
                \Log::error("GreenWeb API token not configured");
                // Still log the OTP if API key is missing
                \Log::info("OTP for {$phoneNumber}: {$otp}");
                return false;
            }

            // Format message
            $message = "Your OTP Code: {$otp}\nValid for 10 minutes. #{$otp}";

            // GreenWeb API endpoint
            $url = "http://api.greenweb.com.bd/api.php";

            // Send via GreenWeb
            $response = \Http::asForm()->post($url, [
                'to' => $phoneNumber,
                'message' => $message,
                'token' => $token,
            ]);

            $result = $response->body();
            
            // GreenWeb returns "Ok" on success
            if (str_starts_with($result, 'Ok')) {
                \Log::info("OTP sent successfully to {$phoneNumber} via GreenWeb");
                return true;
            }

            \Log::error("GreenWeb API failed: " . $result);
            // Log OTP on failure for debugging
            if (config('app.debug')) {
                \Log::info("Failed OTP was: {$otp}");
            }
            return false;

        } catch (\Exception $e) {
            \Log::error("Failed to send OTP via GreenWeb: {$e->getMessage()}");
            // Log OTP on exception for debugging
            if (config('app.debug')) {
                \Log::info("Exception - OTP for {$phoneNumber}: {$otp}");
            }
            return false;
        }
    }

    /**
     * Normalize phone number
     */
    public static function normalizePhone($phone)
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Add country code if missing (assumes Bangladesh +880)
        if (strlen($phone) === 10 && substr($phone, 0, 1) === '0') {
            $phone = '88' . substr($phone, 1);
        } elseif (strlen($phone) === 10) {
            $phone = '88' . $phone;
        }

        return $phone;
    }

    /**
     * Get phone from stored pattern (for testing)
     */
    public static function getTestOtp($phoneNumber)
    {
        $phone = self::normalizePhone($phoneNumber);
        $otpKey = "otp:{$phone}";
        $otpData = Cache::get($otpKey);
        return $otpData['code'] ?? null;
    }
}
