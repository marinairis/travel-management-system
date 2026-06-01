<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordReset extends Notification
{
    protected string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173'));
        $resetUrl = $frontendUrl.'/reset-password/'.$this->token;

        return (new MailMessage)
            ->subject('Redefinição de Senha - Voa - Viagens Corporativas')
            ->view('emails.password-reset', ['resetUrl' => $resetUrl]);
    }
}
