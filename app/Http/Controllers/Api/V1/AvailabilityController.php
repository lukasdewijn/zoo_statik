<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TimeSlotCapacity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AvailabilityController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $date = $validator->validated()['date'] ?? now()->toDateString();

        // 1) haal capacities van die dag op + timeslot label
        $capacities = TimeSlotCapacity::query()
            ->with(['timeSlot:id,label,start_time,end_time,active'])
            ->whereDate('date', $date)
            ->whereHas('timeSlot', fn ($q) => $q->where('active', 1))
            ->get();

        $capacityIds = $capacities->pluck('id');

        // 2) tel gebruikte plekken (aantal visitors) per time_slot_capacity_id in 1 query
        // used = aantal bezoekers (visitors) gekoppeld aan reservations in dat slot
        $usedByCapacityId = DB::table('reservations')
            ->join('visitors', 'visitors.reservation_id', '=', 'reservations.id')
            ->whereIn('reservations.time_slot_capacity_id', $capacityIds)
            ->groupBy('reservations.time_slot_capacity_id')
            ->selectRaw('reservations.time_slot_capacity_id as id, COUNT(*) as used')
            ->pluck('used', 'id');

        $data = $capacities
            ->sortBy(fn ($cap) => $cap->timeSlot?->start_time)
            ->values()
            ->map(function ($cap) use ($usedByCapacityId) {
                $used = (int) ($usedByCapacityId[$cap->id] ?? 0);
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
