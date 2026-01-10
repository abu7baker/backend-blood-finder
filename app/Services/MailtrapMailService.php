<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MailtrapMailService
{
    public function sendOtp(string $toEmail, string $toName, string $otp): bool
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.mailtrap.token'),
            'Content-Type'  => 'application/json',
        ])->post('https://send.api.mailtrap.io/api/send', [
            'from' => [
                'email' => config('mail.from.address'),
                'name'  => config('mail.from.name'),
            ],
            'to' => [
                [
                    'email' => $toEmail,
                    'name'  => $toName,
                ]
            ],
            'subject' => 'رمز التحقق من حسابك',
            'html' => "
                <div style='direction:rtl;font-family:Arial'>
                    <h2>مرحبًا {$toName}</h2>
                    <p>رمز التحقق الخاص بك:</p>
                    <h1 style='color:#d32f2f'>{$otp}</h1>
                    <p>الرمز صالح لمدة 10 دقائق</p>
                </div>
            ",
        ]);

        if (!$response->successful()) {
            Log::error('❌ Mailtrap Email API Error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        }

        return $response->successful();
    }
}
