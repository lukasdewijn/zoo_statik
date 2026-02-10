<?php

namespace Database\Factories;

use App\Models\TimeSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimeSlotFactory extends Factory
{
    protected $model = TimeSlot::class;

    public function definition(): array
    {
        return [
            'start_time' => '09:00',
            'end_time' => '12:00',
            'recurring' => true,
        ];
    }

    public function custom(): static
    {
        return $this->state(['recurring' => false]);
    }
}