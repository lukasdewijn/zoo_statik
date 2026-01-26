<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\TimeSlot;
use App\Rules\SubscriptionNumber;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'timeslot_id' => ['required', 'exists:time_slots,id'],
            'visitors' => ['required', 'array', 'min:1', 'max:10'],

            'visitors.*.first_name' => ['required', 'string'],
            'visitors.*.last_name' => ['required', 'string'],
            'visitors.*.subscription_number' => ['nullable', new SubscriptionNumber()],
        ]);


        $date = $validated['date'];
        $timeslotId = (int)$validated['timeslot_id'];

        // 1) Check: timeslot niet “in het verleden” als date = vandaag
        $slot = TimeSlot::query()->findOrFail($timeslotId);

        if ($date === now()->toDateString()) {
            // Maak een datetime van vandaag + end_time (bv "12:00")
            $end = Carbon::parse($date . ' ' . $slot->end_time);

            if (now()->greaterThanOrEqualTo($end)) {
                return response()->json([
                    'message' => 'This timeslot has already ended for today.',
                    'errors' => [
                        'timeslot_id' => ['Timeslot is no longer bookable today.'],
                    ],
                ], 422);
            }
        }

        // Opslaan in de DB (reservation + visitors) in één transactie
        $reservation = DB::transaction(function () use ($date, $timeslotId, $validated) {
            $reservation = Reservation::create([
                'date' => $date,
                'timeslot_id' => $timeslotId,
                // public_code wordt automatisch gezet in booted()
            ]);

            $reservation->visitors()->createMany(
                collect($validated['visitors'])->map(fn($v) => [
                    'first_name' => $v['first_name'],
                    'last_name' => $v['last_name'],
                    'subscription_nr' => filled($v['subscription_number'] ?? null)
                        ? $v['subscription_number']
                        : null,
                ])->all()
            );

            return $reservation->load(['timeSlot', 'visitors']);
        });

        // Response (201 created)
        return response()->json([
            'data' => [
                'id' => $reservation->id,
                'public_code' => $reservation->public_code,
                'date' => $reservation->date->toDateString(),
                'timeslot' => [
                    'id' => $reservation->timeSlot->id,
                    'label' => $reservation->timeSlot->label,
                    'start_time' => $reservation->timeSlot->start_time,
                    'end_time' => $reservation->timeSlot->end_time,
                ],
                'visitors' => $reservation->visitors->map(fn($v) => [
                    'id' => $v->id,
                    'first_name' => $v->first_name,
                    'last_name' => $v->last_name,
                    'subscription_number' => $v->subscription_nr,
                ])->values(),
                'created_at' => $reservation->created_at?->toISOString(),
            ]
        ], 201);

    }

    public function show(string $public_code)
    {
        $reservation = Reservation::query()
            ->where('public_code', $public_code)
            ->with(['timeSlot', 'visitors'])
            ->first();

        if (!$reservation) {
            return response()->json([
                'message' => 'Reservation not found.',
            ], 404);
        }

        return response()->json([
            'data' => [
                'public_code' => $reservation->public_code,
                'date' => $reservation->date->toDateString(),
                'timeslot' => [
                    'id' => $reservation->timeSlot->id,
                    'label' => $reservation->timeSlot->label,
                    'start_time' => $reservation->timeSlot->start_time,
                    'end_time' => $reservation->timeSlot->end_time,
                ],
                'visitors' => $reservation->visitors->map(fn($v) => [
                    'first_name' => $v->first_name,
                    'last_name' => $v->last_name,
                    'subscription_number' => $v->subscription_nr,
                ])->values(),
                'created_at' => $reservation->created_at?->toISOString(),
            ],
        ], 200);

    }
}
