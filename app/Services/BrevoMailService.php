<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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
        $response = Http::withHeaders([
            'api-key' => $this->apiKey,
            'Accept'  => 'application/json',
            'Content-Type' => 'application/json',
        ])->post($this->endpoint, [
            'sender' => [
                'name'  => 'Blood Finder',
                'email' => 'alhgiabobker213@gmail.com', // نفس الـ sender الموثق
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

        return $response->successful();
    }
}
