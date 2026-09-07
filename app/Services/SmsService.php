<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send an SMS via the REVE SMS gateway (smpp.revesms.com).
     *
     * @param string $to      Destination phone number (e.g. 8801XXXXXXXXX)
     * @param string $message Message body
     * @return array{success: bool, message: string, message_id?: string}
     */
    public static function send(string $to, string $message): array
    {
        $apiKey = config('services.sms.api_key');
        $secretKey = config('services.sms.secret_key');
        $senderId = config('services.sms.sender_id');
        $baseUrl = rtrim(config('services.sms.base_url', 'https://smpp.revesms.com:7790'), '/');

        if (!$apiKey || !$secretKey || !$senderId) {
            Log::error('SMS gateway is not configured (missing api key, secret key, or sender id).');
            Log::info("SMS to {$to}: {$message}");

            return [
                'success' => false,
                'message' => 'SMS gateway is not configured.',
            ];
        }

        try {
            $response = Http::get("{$baseUrl}/sendtext", [
                'apikey' => $apiKey,
                'secretkey' => $secretKey,
                'callerID' => $senderId,
                'toUser' => $to,
                'messageContent' => $message,
            ]);

            $result = $response->json() ?? [];
            $status = (string) ($result['Status'] ?? '');

            // REVE returns Status "0" for accepted messages.
            if ($status === '0') {
                Log::info("SMS sent successfully to {$to} via REVE (message id: " . ($result['Message_ID'] ?? 'n/a') . ')');

                return [
                    'success' => true,
                    'message' => $result['Text'] ?? 'ACCEPTD',
                    'message_id' => $result['Message_ID'] ?? null,
                ];
            }

            Log::error("REVE SMS API failed for {$to}: " . $response->body());

            return [
                'success' => false,
                'message' => $result['Text'] ?? ('SMS gateway error (status ' . $status . ')'),
            ];
        } catch (\Exception $e) {
            Log::error("Failed to send SMS via REVE: {$e->getMessage()}");

            return [
                'success' => false,
                'message' => 'Failed to reach SMS gateway: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Whether the SMS gateway has the minimum required credentials configured.
     */
    public static function isConfigured(): bool
    {
        return (bool) (config('services.sms.api_key')
            && config('services.sms.secret_key')
            && config('services.sms.sender_id'));
    }
}
