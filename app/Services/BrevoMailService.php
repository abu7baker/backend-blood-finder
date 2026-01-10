<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrevoMailService
{
    protected string $apiKey;
    protected string $endpoint = 'https://api.brevo.com/v3/smtp/email';

    public function __construct()
    {
        $this->apiKey = config('services.brevo.key');
    }

    public function sendOtp(string $toEmail, string $toName, string $otp): bool
    {
        if (empty($this->apiKey)) {
            Log::error('❌ Brevo API Key is missing');
            return false;
        }

        $response = Http::withHeaders([
            'api-key' => $this->apiKey,
            'Accept'  => 'application/json',
        ])->post($this->endpoint, [
            'sender' => [
                'name'  => config('services.brevo.sender_name'),
                'email' => config('services.brevo.sender_email'),
            ],
            'to' => [
                [
                    'email' => $toEmail,
                    'name'  => $toName,
                ]
            ],
            'subject' => 'رمز التحقق من حسابك',
            'htmlContent' => "
                <div style='font-family: Arial; direction: rtl'>
                    <h2>مرحبًا {$toName}</h2>
                    <p>رمز التحقق الخاص بك هو:</p>
                    <h1 style='color:#d32f2f'>{$otp}</h1>
                    <p>الرمز صالح لمدة 10 دقائق</p>
                </div>
            ",
        ]);

        if (!$response->successful()) {
            Log::error('❌ Brevo Mail Error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        }

        return $response->successful();
    }
}
