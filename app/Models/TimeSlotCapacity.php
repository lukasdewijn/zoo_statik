<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use App\Notifications\ReservationCancelledByAdmin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class TimeSlotCapacity extends Model
{
    use HasFactory;
    public const DEFAULT_CAPACITY = 200;

    protected $fillable = [
        'date',
        'time_slot_id',
        'capacity',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    protected static function booted(): void
    {
        static::deleting(function (TimeSlotCapacity $capacity) {
            $reservations = Reservation::query()
                ->where('date', $capacity->date)
                ->where('time_slot_id', $capacity->time_slot_id)
                ->where('status', ReservationStatus::Confirmed)
                ->get();

            foreach ($reservations as $reservation) {
                $reservation->update([
                    'status' => ReservationStatus::Cancelled,
                    'cancelled_at' => now(),
                ]);

                if ($reservation->contact_email) {
                    Notification::route('mail', $reservation->contact_email)
                        ->notify(new ReservationCancelledByAdmin($reservation));
                }
            }
        });
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public static function capacityFor(string $date, int $timeSlotId): int
    {
        return (int) (static::query()
            ->whereDate('date', $date)
            ->where('time_slot_id', $timeSlotId)
            ->value('capacity') ?? 0);
    }

    public static function hasCapacityRecord(string $date, int $timeSlotId): bool
    {
        return static::query()
            ->whereDate('date', $date)
            ->where('time_slot_id', $timeSlotId)
            ->exists();
    }

    public static function reservedCount(string $date, int $timeSlotId): int
    {
        return (int) Visitor::query()
            ->join('reservations', 'visitors.reservation_id', '=', 'reservations.id')
            ->whereDate('reservations.date', $date)
            ->where('reservations.time_slot_id', $timeSlotId)
            ->where('reservations.status', ReservationStatus::Confirmed)
            ->count();
    }

    /**
     * Return reserved visitor counts for all timeslots on a given date, keyed by time_slot_id.
     */
    public static function reservedCountsByDate(string $date): \Illuminate\Support\Collection
    {
        return DB::table('reservations')
            ->join('visitors', 'visitors.reservation_id', '=', 'reservations.id')
            ->where('reservations.status', ReservationStatus::Confirmed)
            ->whereDate('reservations.date', $date)
            ->groupBy('reservations.time_slot_id')
            ->select('reservations.time_slot_id', DB::raw('COUNT(visitors.id) as reserved_count'))
            ->get()
            ->pluck('reserved_count', 'time_slot_id');
    }

    public static function remainingCapacity(string $date, int $timeSlotId): int
    {
        return max(0, self::capacityFor($date, $timeSlotId) - self::reservedCount($date, $timeSlotId));
    }

    /**
     * Return an array of Y-m-d date strings that have at least one timeslot with remaining capacity.
     */
    public static function availableDates(int $days = 90): array
    {
        $today = now()->toDateString();
        $end = now()->addDays($days)->toDateString();

        // All capacities in the date range
        $capacities = static::query()
            ->whereDate('date', '>=', $today)
            ->whereDate('date', '<=', $end)
            ->select('date', 'time_slot_id', 'capacity')
            ->get();

        if ($capacities->isEmpty()) {
            return [];
        }

        // Reservation counts grouped by date + timeslot
        $reserved = DB::table('reservations')
            ->join('visitors', 'visitors.reservation_id', '=', 'reservations.id')
            ->where('reservations.status', ReservationStatus::Confirmed)
            ->whereDate('reservations.date', '>=', $today)
            ->whereDate('reservations.date', '<=', $end)
            ->groupBy('reservations.date', 'reservations.time_slot_id')
            ->select(
                'reservations.date',
                'reservations.time_slot_id',
                DB::raw('COUNT(visitors.id) as reserved_count'),
            )
            ->get()
            ->keyBy(fn ($row) => \Carbon\Carbon::parse($row->date)->toDateString() . '_' . $row->time_slot_id);

        $available = [];

        foreach ($capacities as $cap) {
            $dateStr = $cap->date->toDateString();
            $key = $dateStr . '_' . $cap->time_slot_id;
            $reservedCount = $reserved->has($key) ? $reserved->get($key)->reserved_count : 0;

            if ($cap->capacity - $reservedCount > 0) {
                $available[$dateStr] = true;
            }
        }

        return array_keys($available);
    }
}
