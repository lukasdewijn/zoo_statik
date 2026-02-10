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
            ->subject(__('zoo.notifications.reminder.subject'))
            ->greeting(__('zoo.notifications.reminder.greeting'))
            ->line(__('zoo.notifications.reminder.body'))
            ->line(__('zoo.notifications.reminder.reservation_code', ['code' => $this->reservation->public_code]))
            ->line(__('zoo.notifications.reminder.date', ['date' => $this->reservation->date->format('d/m/Y')]))
            ->line(__('zoo.notifications.reminder.timeslot', ['timeslot' => $this->reservation->timeSlot->label]))
            ->line(__('zoo.notifications.reminder.goodbye'));
    }
}
