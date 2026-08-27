<?php

namespace App\Notifications;

use App\Models\JobRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public JobRequest $job,
        public string $heading,
        public string $messageText,
        public string $statusLabel,
        public string $actionUrl,
        public string $notificationType = 'job_status_updated',
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $jobTitle = $this->job->service?->title ?? $this->job->title;

        return (new MailMessage)
            ->subject('Actualización de tu chamba')
            ->view('emails.job-status-updated', [
                'job' => $this->job,
                'heading' => $this->heading,
                'messageText' => $this->messageText,
                'statusLabel' => $this->statusLabel,
                'actionUrl' => $this->actionUrl,
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => $this->notificationType,
            'title' => $this->heading,
            'body' => $this->messageText,
            'url' => $this->actionUrl,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
