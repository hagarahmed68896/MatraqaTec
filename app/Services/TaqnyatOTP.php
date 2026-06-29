<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TaqnyatOTP
{
    protected $sender;
    protected $bearerToken;

    public function __construct()
    {
        $this->sender      = config('services.taqnyat.sender');
        $this->bearerToken = config('services.taqnyat.bearer_token');
    }

    /**
     * Generate a new random 4-digit OTP code.
     */
    public function generateOtp(): string
    {
        return (string) random_int(1000, 9999);
    }

    /**
     * Send OTP via Taqnyat SMS gateway.
     */
    public function sendOTP(string $phone, string $otp): bool
    {
        $phone = ltrim(trim($phone), '+');

        Log::info("[TaqnyatOTP] Sending OTP to: {$phone}");

        // ── REAL SMS via Taqnyat API ──
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->bearerToken,
            'Content-Type'  => 'application/json',
        ])
        ->withoutVerifying()
        ->post('https://api.taqnyat.sa/v1/messages', [
            'recipients' => [$phone],
            'body'       =>
                'رمز التحقق الخاص بك: ' . $otp .
                ' لتسجيل الدخول إلى تطبيق مطرقة: ' . url('/'),
            'sender'   => $this->sender,
            'priority' => 'high',
        ]);

        Log::info("[TaqnyatOTP] API Response: " . $response->body());

        return $response->successful();
    }

    /**
     * Send general SMS via Taqnyat gateway.
     */
    public function sendSMS(string $phone, string $body): bool
    {
        $phone = ltrim(trim($phone), '+');

        Log::info("[TaqnyatOTP] Sending SMS to: {$phone}");

        // Get test numbers from configuration
        $testNumbersStr = config('services.taqnyat.test_numbers', '555555555,0555555555,966555555555');
        $testNumbers = array_map('trim', explode(',', $testNumbersStr));
        $cleanPhone = ltrim(trim($phone), '+');

        $isTestNumber = false;
        foreach ($testNumbers as $testNum) {
            if ($testNum !== '' && str_ends_with($cleanPhone, $testNum)) {
                $isTestNumber = true;
                break;
            }
        }

        if ($isTestNumber) {
            Log::info("[TaqnyatOTP] Static Test Mode - message simulated: {$body}");
            return true;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->bearerToken,
            'Content-Type'  => 'application/json',
        ])
        ->withoutVerifying()
        ->post('https://api.taqnyat.sa/v1/messages', [
            'recipients' => [$phone],
            'body'       => $body,
            'sender'     => $this->sender,
            'priority'   => 'high',
        ]);

        Log::info("[TaqnyatOTP] API Response: " . $response->body());

        return $response->successful();
    }
}
