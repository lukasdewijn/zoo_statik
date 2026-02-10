<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\TimeSlot;
use App\Models\TimeSlotCapacity;
use App\Models\Visitor;
use App\Notifications\ReservationConfirmed;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    /**
     * Create a new reservation with visitors and send a confirmation email.
     *
     * Validates that the timeslot is still bookable, checks remaining capacity
     * using a pessimistic lock to prevent overbooking, and persists the
     * reservation with all associated visitors inside a single transaction.
     *
     * @param  array  $payload  Validated request data (date, time_slot_id, visitors, contact_email)
     * @return Reservation The newly created reservation with timeSlot and visitors loaded.
     *
     * @throws ValidationException When the timeslot has ended or capacity is exceeded.
     */
    public function create(array $payload): Reservation
    {
        $date = $payload['date'];
        $timeslotId = (int) $payload['time_slot_id'];
        $visitors = $payload['visitors'];
        $contactEmail = $payload['contact_email'] ?? null;

        // Ensure the timeslot exists and hasn't already ended for today's date.
        $slot = TimeSlot::query()->findOrFail($timeslotId);

        if ($date === now()->toDateString()) {
            $end = Carbon::parse($date.' '.$slot->end_time);
            if (now()->greaterThanOrEqualTo($end)) {
                throw ValidationException::withMessages([
                    'time_slot_id' => ['Timeslot is no longer bookable today.'],
                ]);
            }
        }

        // Wrap the capacity check + reservation creation in a transaction with
        // a pessimistic lock (SELECT ... FOR UPDATE) to prevent race conditions
        // where two concurrent requests could both pass the capacity check.
        $reservation = DB::transaction(function () use ($date, $timeslotId, $visitors, $contactEmail) {

            // Try to lock the existing capacity row for this date/timeslot combo.
            $capRow = TimeSlotCapacity::query()
                ->whereDate('date', $date)
                ->where('time_slot_id', $timeslotId)
                ->lockForUpdate()
                ->first();

            // If no capacity row exists yet, create one and lock it.
            // The try-catch handles the rare race condition where two requests
            // both pass the check and try to create simultaneously.
            if (! $capRow) {
                try {
                    $capRow = TimeSlotCapacity::create([
                        'date' => $date,
                        'time_slot_id' => $timeslotId,
                        'capacity' => TimeSlotCapacity::DEFAULT_CAPACITY,
                    ]);
                } catch (\Illuminate\Database\UniqueConstraintViolationException) {
                    $capRow = TimeSlotCapacity::query()
                        ->whereDate('date', $date)
                        ->where('time_slot_id', $timeslotId)
                        ->first();
                }

                $capRow = TimeSlotCapacity::query()
                    ->whereKey($capRow->id)
                    ->lockForUpdate()
                    ->first();
            }

            $capacity = (int) $capRow->capacity;

            // Count how many visitors are already booked (only confirmed reservations).
            $currentCount = TimeSlotCapacity::reservedCount($date, $timeslotId);
            $newCount = count($visitors);

            if ($currentCount + $newCount > $capacity) {
                throw ValidationException::withMessages([
                    'time_slot_id' => ["Timeslot is full (capacity {$capacity})."],
                ]);
            }

            $reservation = Reservation::create([
                'date' => $date,
                'time_slot_id' => $timeslotId,
                'contact_email' => $contactEmail,
                'status' => ReservationStatus::Confirmed,
            ]);

            foreach ($visitors as $v) {
                Visitor::create([
                    'reservation_id' => $reservation->id,
                    'first_name' => $v['first_name'],
                    'last_name' => $v['last_name'],
                    'subscription_number' => $v['subscription_number'] ?? null,
                ]);
            }

            return $reservation->load(['timeSlot', 'visitors']);
        });

        // Send confirmation email outside the transaction so a mail failure
        // doesn't roll back the reservation.
        if ($reservation->contact_email) {
            Notification::route('mail', $reservation->contact_email)
                ->notify(new ReservationConfirmed($reservation));
        }

        return $reservation;
    }
}
