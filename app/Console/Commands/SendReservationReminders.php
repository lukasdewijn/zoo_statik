<?php

namespace App\Console\Commands;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Notifications\ReservationReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendReservationReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stuur herinneringsmails voor reservaties van morgen';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $reservations = Reservation::query()
            ->where('date', now()->addDay()->toDateString())
            ->where('status', ReservationStatus::Confirmed)
            ->get();

        foreach ($reservations as $reservation) {
            if ($reservation->contact_email) {
                Notification::route('mail', $reservation->contact_email)
                    ->notify(new ReservationReminder($reservation));
            }
        }

        $this->info("Reminders verstuurd voor {$reservations->count()} reservaties");
    }
}
