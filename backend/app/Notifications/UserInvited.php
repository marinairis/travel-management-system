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
        $inviteUrl = $frontendUrl . '/invitation/' . $this->token;

        return (new MailMessage)
            ->subject('Convite para o Sistema de Viagens Corporativas')
            ->greeting('Olá!')
            ->line('Você foi convidado para acessar o Sistema de Viagens Corporativas.')
            ->line('Clique no botão abaixo para criar sua conta e definir sua senha.')
            ->action('Aceitar Convite', $inviteUrl)
            ->line('Este convite expira em 7 dias.')
            ->line('Se você não esperava este convite, pode ignorar este email.');
    }
}
