<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ChambappNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $notificationType,
        public string $title,
        public string $body,
        public string $url,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => $this->notificationType,
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
