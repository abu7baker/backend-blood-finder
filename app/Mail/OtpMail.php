<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;
    public string $name;

    public function __construct(string $otp, string $name)
    {
        $this->otp = $otp;
        $this->name = $name;
    }

    public function build()
    {
        return $this->subject('رمز التحقق من حسابك')
            ->html("
                <div style='font-family:Arial; direction:rtl'>
                    <h2>مرحبًا {$this->name}</h2>
                    <p>رمز التحقق الخاص بك هو:</p>
                    <h1 style='color:#d32f2f'>{$this->otp}</h1>
                    <p>الرمز صالح لمدة 10 دقائق</p>
                </div>
            ");
    }
}
