<?php

namespace App\Console\Commands;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Illuminate\Console\Command;

class CleanupExpiredReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:cleanup-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Annuleer reservaties waarvan de datum verstreken is';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = Reservation::query()
            ->where('date', '<', now()->toDateString())
            ->where('status', ReservationStatus::Confirmed)
            ->update([
                'status' => ReservationStatus::Completed,
            ]);

        $this->info("$count verlopen reservaties afgesloten.");
    }
}
