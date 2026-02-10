<?php

// app/Http/Controllers/ReservationCancelController.php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class ReservationCancelController extends Controller
{
    public function show(string $public_code)
    {
        $reservation = Reservation::with('timeSlot')->where('public_code', $public_code)->firstOrFail();

        if ($reservation->status === ReservationStatus::Cancelled) {
            return redirect()->route('reservations.cancel.done', $public_code);
        }

        $cancelUrl = URL::temporarySignedRoute(
            'reservations.cancel.do',
            now()->addDays(7),
            ['public_code' => $public_code]
        );

        return view('reservations.cancel', compact('reservation', 'cancelUrl'));
    }

    public function cancel(Request $request, string $public_code)
    {
        $reservation = Reservation::where('public_code', $public_code)->firstOrFail();

        if ($reservation->status === ReservationStatus::Cancelled) {
            return redirect()
                ->route('reservations.cancel.done', $public_code)
                ->with('success', 'Deze reservatie was al geannuleerd.');
        }

        $reservation->update([
            'status' => ReservationStatus::Cancelled,
            'cancelled_at' => now(),
        ]);

        return redirect()
            ->route('reservations.cancel.done', $public_code)
            ->with('success', 'Je reservatie is geannuleerd.');
    }

    public function done(string $public_code)
    {
        $reservation = Reservation::with('timeSlot')->where('public_code', $public_code)->firstOrFail();

        return view('reservations.cancelled', compact('reservation'));
    }
}
