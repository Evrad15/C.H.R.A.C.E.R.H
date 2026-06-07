<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class HorizonAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $subject,
        protected string $body,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->line($this->body)
            ->line('Ceci est une notification automatique du système Horizon.');
    }
}
