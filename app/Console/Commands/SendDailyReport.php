<?php

namespace App\Console\Commands;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\TimeSlotCapacity;
use App\Models\Visitor;
use App\Notifications\DailyVisitorReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendDailyReport extends Command
{
    protected $signature = 'analytics:daily-report';

    protected $description = 'Stuur een dagelijks bezoekersrapport naar de admin';

    public function handle()
    {
        $adminEmail = config('mail.admin_email');

        if (! $adminEmail) {
            $this->error('Geen admin e-mail geconfigureerd. Stel ADMIN_EMAIL in via je .env bestand.');

            return self::FAILURE;
        }

        $today = now()->toDateString();

        $totalVisitors = Visitor::query()
            ->join('reservations', 'visitors.reservation_id', '=', 'reservations.id')
            ->where('reservations.date', $today)
            ->whereIn('reservations.status', [ReservationStatus::Confirmed, ReservationStatus::Completed])
            ->count();

        $totalReservations = Reservation::query()
            ->where('date', $today)
            ->whereIn('status', [ReservationStatus::Confirmed, ReservationStatus::Completed])
            ->count();

        $capacities = TimeSlotCapacity::query()
            ->where('date', $today)
            ->with('timeSlot')
            ->get();

        $timeslotStats = [];

        foreach ($capacities as $cap) {
            $visitors = Visitor::query()
                ->join('reservations', 'visitors.reservation_id', '=', 'reservations.id')
                ->where('reservations.date', $today)
                ->where('reservations.time_slot_id', $cap->time_slot_id)
                ->whereIn('reservations.status', [ReservationStatus::Confirmed, ReservationStatus::Completed])
                ->count();

            $timeslotStats[] = [
                'label' => $cap->timeSlot->label,
                'visitors' => $visitors,
                'capacity' => $cap->capacity,
            ];
        }

        Notification::route('mail', $adminEmail)
            ->notify(new DailyVisitorReport(
                $totalVisitors,
                $totalReservations,
                $timeslotStats,
                now()->format('d/m/Y'),
            ));

        $this->info("Dagelijks rapport verstuurd naar {$adminEmail}.");

        return self::SUCCESS;
    }
}
