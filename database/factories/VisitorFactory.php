<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Visitor;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisitorFactory extends Factory
{
    protected $model = Visitor::class;

    public function definition(): array
    {
        return [
            'reservation_id' => Reservation::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'subscription_number' => null,
        ];
    }
}
