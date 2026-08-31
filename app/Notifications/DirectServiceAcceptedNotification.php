<?php

namespace App\Notifications;

use App\Models\JobRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DirectServiceAcceptedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public JobRequest $job) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $serviceTitle = $this->job->service?->title ?? $this->job->title;

        return (new MailMessage)
            ->subject('¡Tu solicitud de servicio fue aceptada!')
            ->view('emails.direct-service-accepted', ['job' => $this->job, 'serviceTitle' => $serviceTitle]);
    }

    public function toDatabase(object $notifiable): array
    {
        $serviceTitle = $this->job->service?->title ?? $this->job->title;
        $proName = $this->job->professional?->user?->name ?? 'El profesional';

        return [
            'type' => 'job_awaiting_payment',
            'title' => '¡Solicitud aceptada!',
            'body' => "{$proName} aceptó tu solicitud para \"{$serviceTitle}\". Paga tu chamba para formalizar la contratación.",
            'url' => route('job-requests.show', $this->job),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
