<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyVisitorReport extends Notification
{
    use Queueable;

    public function __construct(
        public int $totalVisitors,
        public int $totalReservations,
        public array $timeslotStats,
        public string $date,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Dagelijks rapport - {$this->date}")
            ->greeting("Overzicht van {$this->date}")
            ->line("Totaal aantal bezoekers: **{$this->totalVisitors}**")
            ->line("Totaal aantal reservaties: **{$this->totalReservations}**")
            ->line('---')
            ->line('**Bezettingsgraad per tijdslot:**');

        foreach ($this->timeslotStats as $stat) {
            $percentage = $stat['capacity'] > 0
                ? round(($stat['visitors'] / $stat['capacity']) * 100)
                : 0;

            $mail->line("{$stat['label']}: {$stat['visitors']}/{$stat['capacity']} bezoekers ({$percentage}%)");
        }

        return $mail;
    }
}
