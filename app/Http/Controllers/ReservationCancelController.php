<?php

// app/Http/Controllers/ReservationCancelController.php
namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationCancelController extends Controller
{
    public function show(string $public_code)
    {
        $reservation = Reservation::where('public_code', $public_code)->firstOrFail();

        return view('reservations.cancel', compact('reservation'));
    }

    public function cancel(Request $request, string $public_code)
    {
        $reservation = Reservation::where('public_code', $public_code)->firstOrFail();

        if ($reservation->status === 'cancelled') {
            return redirect()
                ->route('reservations.cancel.done', $public_code)
                ->with('success', 'Deze reservatie was al geannuleerd.');
        }

        $reservation->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return redirect()
            ->route('reservations.cancel.done', $public_code)
            ->with('success', 'Je reservatie is geannuleerd.');
    }

    public function done(string $public_code)
    {
        $reservation = Reservation::where('public_code', $public_code)->firstOrFail();

        return view('reservations.cancelled', compact('reservation'));
    }
}
