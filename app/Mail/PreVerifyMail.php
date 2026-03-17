<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PreVerifyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $verificationUrl,
        public readonly string $email,
    ) {}

    public function build(): static
    {
        return $this->subject('Verify Your Email')
            ->view('mail.verify')
            ->with([
                'title' => 'Verify Your Email',
                'message' => 'Please confirm your email to continue.',
                'action_url' => $this->verificationUrl,
                'action_text' => 'Verify Email',
                'email' => $this->email,
            ]);
    }
}
