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
            'firstname' => fake()->firstName(),
            'lastname' => fake()->lastName(),
            'subscription_nr' => null,
        ];
    }
}
