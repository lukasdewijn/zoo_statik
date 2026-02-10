<?php

// app/Notifications/ReservationConfirmed.php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class ReservationConfirmed extends Notification
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
        $cancelUrl = URL::temporarySignedRoute(
            'reservations.cancel.show',
            now()->addDays(7),
            ['public_code' => $this->reservation->public_code]
        );

        return (new MailMessage)
            ->subject(__('zoo.notifications.confirmed.subject'))
            ->greeting(__('zoo.notifications.confirmed.greeting'))
            ->line(__('zoo.notifications.confirmed.body'))
            ->line(__('zoo.notifications.confirmed.reservation_code', ['code' => $this->reservation->public_code]))
            ->line(__('zoo.notifications.confirmed.date', ['date' => $this->reservation->date->format('d/m/Y')]))
            ->line(__('zoo.notifications.confirmed.timeslot', ['timeslot' => $this->reservation->timeSlot->label]))
            ->action(__('zoo.notifications.confirmed.cancel_action'), $cancelUrl)
            ->line(__('zoo.notifications.confirmed.cancel_notice'));
    }
}
