<?php

namespace App\Http\Controllers;

use App\Models\Reservation;

class ReservationSuccessController extends Controller
{
    public function __invoke(string $public_code)
    {
        $reservation = Reservation::query()
            ->where('public_code', $public_code)
            ->with(['timeSlot', 'visitors'])
            ->firstOrFail();

        return view('reservations.success', compact('reservation'));
    }
}
