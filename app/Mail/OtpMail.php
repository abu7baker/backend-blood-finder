<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class OtpMail extends Mailable
{
    public function __construct(
        public string $otp,
        public string $name
    ) {}

    public function build()
    {
        return $this
            ->subject('رمز التحقق من حسابك')
            ->html("
                <div style='font-family:Arial;direction:rtl'>
                    <h2>مرحباً {$this->name}</h2>
                    <p>رمز التحقق الخاص بك هو:</p>
                    <h1 style='color:#c00'>{$this->otp}</h1>
                    <p>الرمز صالح لمدة 10 دقائق</p>
                </div>
            ");
    }
}
