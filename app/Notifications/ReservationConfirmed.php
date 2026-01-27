<?php

// app/Notifications/ReservationConfirmed.php
namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class ReservationConfirmed extends Notification
{
    use Queueable;

    public function __construct(public Reservation $reservation) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $cancelUrl = URL::temporarySignedRoute(
            'reservations.cancel.show',
            now()->addDays(7),
            ['public_code' => $this->reservation->public_code]
        );

        return (new MailMessage)
            ->subject('Bevestiging van je reservatie')
            ->greeting('Hey!')
            ->line("Je reservatie is bevestigd.")
            ->line("Reservatiecode: {$this->reservation->public_code}")
            ->line("Datum: {$this->reservation->date->format('d/m/Y')}")
            ->line("Tijdslot ID: {$this->reservation->timeslot_id}") // later mooier met start/end
            ->action('Annuleer via deze link', $cancelUrl)
            ->line('Deze annuleerlink is tijdelijk geldig.');
    }
}
