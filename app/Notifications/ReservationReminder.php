<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationReminder extends Notification
{
    use Queueable;

    public function __construct(public Reservation $reservation)
    {
        $this->reservation->loadMissing('timeSlot');
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Herinnering: je bezoek aan de Zoo is morgen!')
            ->greeting('Hey!')
            ->line('Dit is een vriendelijke herinnering dat je morgen een bezoek gepland hebt aan de Zoo.')
            ->line("Reservatiecode: {$this->reservation->public_code}")
            ->line("Datum: {$this->reservation->date->format('d/m/Y')}")
            ->line("Tijdslot: {$this->reservation->timeSlot->label}")
            ->line('Tot morgen!');
    }
}
