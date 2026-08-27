<?php

namespace App\Notifications;

use App\Models\JobRequest;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentConfirmedProfessionalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public JobRequest $job,
        public Payment $payment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $jobTitle = $this->job->service?->title ?? $this->job->title;

        return (new MailMessage)
            ->subject('El cliente realizó el pago')
            ->view('emails.payment-confirmed-professional', [
                'job' => $this->job,
                'payment' => $this->payment,
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        $clientName = $this->job->client?->name ?? 'El cliente';
        $jobTitle = $this->job->service?->title ?? $this->job->title;

        return [
            'type' => 'payment_confirmed_professional',
            'title' => 'El cliente realizó el pago',
            'body' => "{$clientName} pagó la chamba \"{$jobTitle}\". Ya puedes iniciar el trabajo.",
            'url' => route('job-requests.show', $this->job),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
