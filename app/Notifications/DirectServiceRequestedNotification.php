<?php

namespace App\Notifications;

use App\Models\JobRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DirectServiceRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public JobRequest $job) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nueva solicitud de servicio en Chambapp')
            ->view('emails.direct-service-requested', ['job' => $this->job]);
    }

    public function toDatabase(object $notifiable): array
    {
        $serviceTitle = $this->job->service?->title ?? $this->job->title;

        return [
            'type' => 'direct_service_request',
            'title' => 'Nueva solicitud de servicio',
            'body' => "Un cliente solicitó tu servicio: {$serviceTitle}",
            'url' => route('job-requests.show', $this->job),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
