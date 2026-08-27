<?php

namespace App\Notifications;

use App\Models\JobRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public JobRequest $job) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $jobTitle = $this->job->service?->title ?? $this->job->title;

        return (new MailMessage)
            ->subject('Chamba completada — ¡califica tu experiencia!')
            ->view('emails.job-completed', ['job' => $this->job]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'review_requested',
            'title' => '¿Cómo fue tu experiencia?',
            'body' => 'Califica al profesional para ayudar a otros clientes.',
            'url' => route('reviews.create', $this->job),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
