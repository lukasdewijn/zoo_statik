<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationCancelledByAdmin extends Notification
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
            ->subject('Je reservatie is geannuleerd')
            ->greeting('Hey!')
            ->line('Het spijt ons, maar je reservatie is geannuleerd door de beheerder.')
            ->line("Reservatiecode: {$this->reservation->public_code}")
            ->line("Datum: {$this->reservation->date->format('d/m/Y')}")
            ->line("Tijdslot: {$this->reservation->timeSlot->label} ({$this->reservation->timeSlot->start_time} - {$this->reservation->timeSlot->end_time})")
            ->action('Boek een nieuwe reservatie', url('/reservation'))
            ->line('Onze excuses voor het ongemak.');
    }
}
