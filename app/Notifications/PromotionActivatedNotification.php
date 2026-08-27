<?php

namespace App\Notifications;

use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PromotionActivatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Service $service,
        public int $days,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('¡Tu promoción está activa!')
            ->view('emails.promotion-activated', [
                'service' => $this->service,
                'days' => $this->days,
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'promotion_activated',
            'title' => '¡Promoción activada!',
            'body' => "Tu servicio \"{$this->service->title}\" ha sido destacado por {$this->days} días.",
            'url' => route('marketplace.service', $this->service),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
