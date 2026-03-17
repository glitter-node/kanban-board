<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailCustom extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Email Verification Required')
            ->view('mail.verify', [
                'title' => 'Verify Your Email',
                'content' => 'Please confirm your email to continue.',
                'action_url' => $this->verificationUrl($notifiable),
                'action_text' => 'Verify Email',
            ]);
    }
}
