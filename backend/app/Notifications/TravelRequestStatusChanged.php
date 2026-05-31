<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\TravelRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TravelRequestStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected TravelRequest $travelRequest;
    protected string $oldStatus;

    public function __construct(TravelRequest $travelRequest, string $oldStatus)
    {
        $this->travelRequest = $travelRequest;
        $this->oldStatus = $oldStatus;
    }

    public function via(mixed $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toDatabase(mixed $notifiable): array
    {
        return [
            'travel_request_id' => $this->travelRequest->id,
            'destination'       => $this->travelRequest->destination,
            'old_status'        => $this->oldStatus,
            'new_status'        => $this->travelRequest->status,
            'departure_date'    => $this->travelRequest->departure_date?->format('Y-m-d'),
        ];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Status do Pedido de Viagem Atualizado')
            ->greeting('Olá, ' . e($notifiable->name) . '!')
            ->line('O status do seu pedido de viagem foi atualizado.')
            ->line('**Destino:** ' . e($this->travelRequest->destination))
            ->line('**Status anterior:** ' . $this->translateStatus($this->oldStatus))
            ->line('**Novo status:** ' . $this->translateStatus($this->travelRequest->status))
            ->line('**Data de ida:** ' . $this->travelRequest->departure_date?->format('d/m/Y'))
            ->line('**Data de volta:** ' . $this->travelRequest->return_date?->format('d/m/Y'))
            ->action('Ver Pedido', url('/'))
            ->line('Obrigado por usar nosso sistema!');
    }

    private function translateStatus(string $status): string
    {
        $translations = [
            'requested' => 'Solicitado',
            'approved' => 'Aprovado',
            'cancelled' => 'Cancelado',
        ];

        return $translations[$status] ?? $status;
    }
}
