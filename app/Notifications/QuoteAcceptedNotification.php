<?php

namespace App\Notifications;

use App\Models\JobQuote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuoteAcceptedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public JobQuote $quote) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $jobTitle = $this->quote->jobRequest?->service?->title ?? $this->quote->jobRequest?->title;

        return (new MailMessage)
            ->subject("¡Cotización aceptada en Chambapp! — {$jobTitle}")
            ->view('emails.quote-accepted', ['quote' => $this->quote]);
    }

    public function toDatabase(object $notifiable): array
    {
        $clientName = $this->quote->jobRequest?->client?->name ?? 'El cliente';

        return [
            'type' => 'quote_accepted',
            'title' => '¡Cotización aceptada!',
            'body' => "{$clientName} aceptó tu cotización. El trabajo está pendiente de pago.",
            'url' => route('job-requests.show', $this->quote->jobRequest),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
