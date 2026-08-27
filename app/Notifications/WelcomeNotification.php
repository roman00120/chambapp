<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $user) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('¡Bienvenido a Chambapp!')
            ->view('emails.welcome', ['user' => $this->user]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'welcome',
            'title' => '¡Bienvenido a Chambapp!',
            'body' => 'Tu cuenta ha sido creada exitosamente.',
            'url' => route('home'),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
