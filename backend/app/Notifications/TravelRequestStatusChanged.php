<?php

namespace App\Notifications;

use App\Models\TravelRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TravelRequestStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected $travelRequest;
    protected $oldStatus;

    public function __construct(TravelRequest $travelRequest, $oldStatus)
    {
        $this->travelRequest = $travelRequest;
        $this->oldStatus = $oldStatus;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $status = $this->travelRequest->status === 'approved' ? 'aprovado' : 'cancelado';
        $color = $this->travelRequest->status === 'approved' ? 'green' : 'red';

        return (new MailMessage)
            ->subject('Status do Pedido de Viagem Atualizado')
            ->greeting('Olá, ' . $notifiable->name . '!')
            ->line('O status do seu pedido de viagem foi atualizado.')
            ->line('**Destino:** ' . $this->travelRequest->destination)
            ->line('**Status anterior:** ' . $this->translateStatus($this->oldStatus))
            ->line('**Novo status:** ' . $this->translateStatus($this->travelRequest->status))
            ->line('**Data de ida:** ' . $this->travelRequest->departure_date->format('d/m/Y'))
            ->line('**Data de volta:** ' . $this->travelRequest->return_date->format('d/m/Y'))
            ->action('Ver Pedido', url('/'))
            ->line('Obrigado por usar nosso sistema!');
    }

    private function translateStatus($status)
    {
        $translations = [
            'requested' => 'Solicitado',
            'approved' => 'Aprovado',
            'cancelled' => 'Cancelado',
        ];

        return $translations[$status] ?? $status;
    }
}
