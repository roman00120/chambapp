<?php

namespace App\Notifications;

use App\Models\JobRequest;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentConfirmedClientNotification extends Notification implements ShouldQueue
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
            ->subject('Pago confirmado')
            ->view('emails.payment-confirmed-client', [
                'job' => $this->job,
                'payment' => $this->payment,
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        $jobTitle = $this->job->service?->title ?? $this->job->title;
        $total = number_format((float) ($this->payment->customer_total ?? $this->payment->gross_amount), 2);

        return [
            'type' => 'payment_confirmed_client',
            'title' => 'Pago confirmado',
            'body' => "Tu pago de \${$total} MXN para \"{$jobTitle}\" está en custodia.",
            'url' => route('job-requests.show', $this->job),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
