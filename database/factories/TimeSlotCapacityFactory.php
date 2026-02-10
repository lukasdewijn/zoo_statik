<?php

namespace Database\Factories;

use App\Models\TimeSlot;
use App\Models\TimeSlotCapacity;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimeSlotCapacityFactory extends Factory
{
    protected $model = TimeSlotCapacity::class;

    public function definition(): array
    {
        return [
            'date' => now()->addDay()->toDateString(),
            'time_slot_id' => TimeSlot::factory(),
            'capacity' => TimeSlotCapacity::DEFAULT_CAPACITY,
        ];
    }
}
