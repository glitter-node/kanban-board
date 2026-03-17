<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordCustom extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reset Your Password')
            ->view('mail.password_reset', [
                'title' => 'Reset Your Password',
                'content' => 'You requested a password reset.',
                'action_url' => $this->resetUrl($notifiable),
                'action_text' => 'Reset Password',
            ]);
    }
}
