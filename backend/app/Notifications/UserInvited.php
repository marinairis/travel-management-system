<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserInvited extends Notification
{
    protected string $token;

    protected string $role;

    public function __construct(string $token, string $role)
    {
        $this->token = $token;
        $this->role = $role;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173'));
        $inviteUrl = $frontendUrl.'/invitation/'.$this->token;

        return (new MailMessage)
            ->subject('Convite para o Voa - Viagens Corporativas')
            ->view('emails.user-invited', ['inviteUrl' => $inviteUrl]);
    }
}
