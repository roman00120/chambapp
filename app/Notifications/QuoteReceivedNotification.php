<?php

namespace App\Notifications;

use App\Models\JobQuote;
use App\ValueObjects\PaymentCalculation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuoteReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public JobQuote $quote,
        public ?PaymentCalculation $breakdown = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $jobTitle = $this->quote->jobRequest?->service?->title ?? $this->quote->jobRequest?->title;

        return (new MailMessage)
            ->subject('Nueva cotización en Chambapp')
            ->view('emails.quote-received', [
                'quote' => $this->quote,
                'breakdown' => $this->breakdown,
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        $proName = $this->quote->professional?->user?->name ?? 'Un profesional';
        $amount = number_format((float) $this->quote->amount, 2);

        return [
            'type' => 'quote_received',
            'title' => 'Recibiste una nueva cotización',
            'body' => "{$proName} envió una cotización de \${$amount} MXN.",
            'url' => route('job-requests.show', $this->quote->jobRequest),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
