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
            ->subject(__('zoo.notifications.cancelled_by_admin.subject'))
            ->greeting(__('zoo.notifications.cancelled_by_admin.greeting'))
            ->line(__('zoo.notifications.cancelled_by_admin.body'))
            ->line(__('zoo.notifications.cancelled_by_admin.reservation_code', ['code' => $this->reservation->public_code]))
            ->line(__('zoo.notifications.cancelled_by_admin.date', ['date' => $this->reservation->date->format('d/m/Y')]))
            ->line(__('zoo.notifications.cancelled_by_admin.timeslot', ['timeslot' => $this->reservation->timeSlot->label]))
            ->action(__('zoo.notifications.cancelled_by_admin.new_reservation'), url('/reservation'))
            ->line(__('zoo.notifications.cancelled_by_admin.apology'));
    }
}
