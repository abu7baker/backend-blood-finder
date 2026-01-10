<?php

namespace App\Services;

use Mailtrap\Client;
use Mailtrap\Email\Address;
use Mailtrap\Email\Email;
use Illuminate\Support\Facades\Log;

class MailtrapMailService
{
    public function sendOtp(string $toEmail, string $toName, string $otp): bool
    {
        try {
            $client = new Client([
                'api_key' => config('services.mailtrap.token'),
            ]);

            $email = (new Email())
                ->from(new Address(
                    config('mail.from.address'),
                    config('mail.from.name')
                ))
                ->to(new Address($toEmail, $toName))
                ->subject('رمز التحقق من حسابك')
                ->html("
                    <div style='font-family:Arial; direction:rtl'>
                        <h3>مرحبًا {$toName}</h3>
                        <p>رمز التحقق الخاص بك:</p>
                        <h1 style='color:#d32f2f'>{$otp}</h1>
                        <p>الرمز صالح لمدة 10 دقائق</p>
                    </div>
                ");

            $client->send($email);

            return true;

        } catch (\Throwable $e) {
            Log::error('❌ Mailtrap SDK Error', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
