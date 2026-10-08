<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $resetUrl;
    public string $name;

    public function __construct(string $resetUrl, string $name = 'Người dùng')
    {
        $this->resetUrl = $resetUrl;
        $this->name = $name;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Hướng dẫn đặt lại mật khẩu - Hệ thống Quản lý Khóa luận',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reset_password',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
