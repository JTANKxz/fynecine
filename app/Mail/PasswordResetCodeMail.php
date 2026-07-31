<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code;
    public $purpose;

    public function __construct($code, string $purpose = 'reset')
    {
        $this->code = $code;
        $this->purpose = $purpose;
    }

    public function envelope(): Envelope
    {
        $subject = $this->purpose === 'activation'
            ? 'Confirme sua compra - '
            : 'Seu código de recuperação - ';

        return new Envelope(subject: $subject . config('app.name'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.password-reset-code');
    }

    public function attachments(): array
    {
        return [];
    }
}