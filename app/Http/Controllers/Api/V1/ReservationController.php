<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ReservationResource;
use App\Models\Reservation;
use App\Rules\DateHasAvailability;
use App\Rules\SubscriptionNumber;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    public function store(Request $request, ReservationService $service)
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', new DateHasAvailability],
            'time_slot_id' => ['required', 'exists:time_slots,id'],
            'visitors' => ['required', 'array', 'min:1', 'max:200'],
            'visitors.*.first_name' => ['required', 'string', 'min:1', 'max:255'],
            'visitors.*.last_name' => ['required', 'string', 'min:1', 'max:255'],
            'visitors.*.subscription_number' => ['nullable', new SubscriptionNumber],
            'contact_email' => ['required', 'email:rfc', 'max:255'],
        ]);

        try {
            $reservation = $service->create($validated);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error.',
                'errors' => $e->errors(),
            ], 422);
        }

        return (new ReservationResource($reservation))
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $public_code)
    {
        $reservation = Reservation::query()
            ->where('public_code', $public_code)
            ->with(['timeSlot', 'visitors'])
            ->firstOrFail();

        return new ReservationResource($reservation);
    }

    public function cancel(Request $request, string $public_code)
    {
        $request->validate([
            'contact_email' => ['required', 'email:rfc', 'max:255'],
        ]);

        $reservation = Reservation::where('public_code', $public_code)->firstOrFail();

        if (strtolower($reservation->contact_email) !== strtolower($request->input('contact_email'))) {
            return response()->json([
                'message' => 'Het opgegeven e-mailadres komt niet overeen.',
            ], 403);
        }

        if ($reservation->status === ReservationStatus::Cancelled) {
            return response()->json([
                'message' => 'Deze reservatie is al geannuleerd.',
            ], 409);
        }

        $reservation->update([
            'status' => ReservationStatus::Cancelled,
            'cancelled_at' => now(),
        ]);

        $reservation->load(['timeSlot', 'visitors']);

        return new ReservationResource($reservation);
    }
}
