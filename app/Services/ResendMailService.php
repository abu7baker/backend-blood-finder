<?php

namespace App\Services;

use Resend;
use Illuminate\Support\Facades\Log;

class ResendMailService
{
    public function sendOtp(string $toEmail, string $toName, string $otp): bool
    {
        try {
            $resend = Resend::client(config('services.resend.key'));

            $resend->emails->send([
                'from' => config('services.resend.from_name') .
                          ' <' . config('services.resend.from_email') . '>',
                'to' => [$toEmail],
                'subject' => 'رمز التحقق من حسابك',
                'html' => "
                    <div style='font-family:Arial; direction:rtl'>
                        <h2>مرحبًا {$toName}</h2>
                        <p>رمز التحقق الخاص بك هو:</p>
                        <h1 style='color:#d32f2f'>{$otp}</h1>
                        <p>الرمز صالح لمدة 10 دقائق</p>
                    </div>
                ",
            ]);

            return true;

        } catch (\Throwable $e) {
            Log::error('❌ Resend Mail Error', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
