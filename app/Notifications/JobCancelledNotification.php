<?php

namespace App\Notifications;

use App\Models\JobRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public JobRequest $job,
        public ?string $reason = null,
        public ?string $actionUrl = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $jobTitle = $this->job->service?->title ?? $this->job->title;

        return (new MailMessage)
            ->subject("Cancelación de chamba — {$jobTitle}")
            ->view('emails.job-cancelled', [
                'job' => $this->job,
                'reason' => $this->reason,
                'actionUrl' => $this->actionUrl ?? route('home'),
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        $jobTitle = $this->job->service?->title ?? $this->job->title;

        return [
            'type' => 'job_cancelled',
            'title' => 'Chamba cancelada',
            'body' => "La chamba \"{$jobTitle}\" ha sido cancelada.",
            'url' => $this->actionUrl ?? route('home'),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
