<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Support\Facades\Http;

class ReservationSuccessController extends Controller
{
    public function __invoke(string $public_code)
    {
        $reservation = Reservation::query()
            ->where('public_code', $public_code)
            ->with(['timeSlot', 'visitors'])
            ->firstOrFail();

        $capybara = $this->loadCapybaraOfTheDay();

        return view('reservations.success', compact('reservation', 'capybara'));
    }

    private function loadCapybaraOfTheDay(): ?array
    {
        try {
            $imageResponse = Http::timeout(3)->get('https://api.capy.lol/v1/capyoftheday?json=true');
            $factResponse = Http::timeout(3)->get('https://api.capy.lol/v1/fact');

            $capybara = [];

            if ($imageResponse->successful()) {
                $capybara['url'] = $imageResponse->json('data.url');
                $capybara['alt'] = $imageResponse->json('data.alt') ?? 'Capybara of the day';
            }

            if ($factResponse->successful()) {
                $capybara['fact'] = $factResponse->json('data.fact');
            }

            return $capybara ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
