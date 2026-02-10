<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AvailabilityRequest;
use App\Models\TimeSlotCapacity;

class AvailabilityController extends Controller
{
    /**
     * Return the availability for each active timeslot on a given date.
     *
     * Responds with capacity, used spots, available spots and a full flag
     * per timeslot, sorted by start time. Defaults to today when no date
     * is provided.
     */
    public function index(AvailabilityRequest $request)
    {
        $date = $request->validated()['date'] ?? now()->toDateString();

        // Fetch capacity rows for the requested date, only including active timeslots.
        $capacities = TimeSlotCapacity::query()
            ->with(['timeSlot:id,start_time,end_time,recurring'])
            ->whereDate('date', $date)
            ->get();

        // For each timeslot, calculate how many spots are used (confirmed visitors)
        // and how many are still available.
        $data = $capacities
            ->sortBy(fn ($cap) => $cap->timeSlot?->start_time)
            ->values()
            ->map(function ($cap) use ($date) {
                $used = TimeSlotCapacity::reservedCount($date, $cap->time_slot_id);
                $capacity = (int) $cap->capacity;
                $available = max(0, $capacity - $used);

                return [
                    'time_slot_capacity_id' => $cap->id,
                    'time_slot_id' => $cap->time_slot_id,
                    'label' => $cap->timeSlot?->label,
                    'capacity' => $capacity,
                    'used' => $used,
                    'available' => $available,
                    'full' => $available <= 0,
                ];
            });

        return response()->json([
            'meta' => [
                'date' => $date,
            ],
            'data' => $data,
        ]);
    }
}
