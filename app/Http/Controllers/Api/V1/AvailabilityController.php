<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    private const CAPACITY_PER_TIMESLOT = 200;

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

        $validated = $validator->validated();
        $date = $validated['date'] ?? now()->toDateString();

        $timeSlots = TimeSlot::query()
            ->where('active', 1)
            ->get()
            ->map(function ($slot) use ($date) {
                $used = DB::table('reservations')
                    ->join('visitors', 'visitors.reservation_id', '=', 'reservations.id')
                    ->where('reservations.timeslot_id', $slot->id)
                    ->whereDate('reservations.date', $date)
                    ->count();

                return [
                    'id'        => $slot->id,
                    'label'     => $slot->label,
                    'capacity'  => self::CAPACITY_PER_TIMESLOT,
                    'used'      => $used,
                    'available' => max(0, self::CAPACITY_PER_TIMESLOT - $used),
                    'full'      => $used >= self::CAPACITY_PER_TIMESLOT,
                ];
            });

        return response()->json([
            'meta' => [
                'date' => $date,
                'capacity_per_timeslot' => self::CAPACITY_PER_TIMESLOT,
            ],
            'data' => $timeSlots,
        ]);
    }
}
