<?php

namespace Database\Factories;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\TimeSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        return [
            'date' => now()->addDay()->toDateString(),
            'time_slot_id' => TimeSlot::factory(),
            'contact_email' => fake()->safeEmail(),
            'status' => ReservationStatus::Confirmed,
        ];
    }

    public function cancelled(): static
    {
        return $this->state([
            'status' => ReservationStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }
}
