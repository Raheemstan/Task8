<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AbsenceWarning extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly int $absences,
        private readonly string $period
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Attendance Warning')
            ->line("Your child has been absent {$this->absences} times during this {$this->period}.")
            ->line('Please ensure regular attendance to maintain academic progress.');
    }
} 