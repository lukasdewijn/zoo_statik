<?php

namespace Database\Seeders;

use App\Models\TimeSlot;
use Illuminate\Database\Seeder;

class TimeSlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timeslots = [
            [
                'start_time' => '10:00',
                'end_time' => '12:00',
                'recurring' => true,
            ],
            [
                'start_time' => '12:00',
                'end_time' => '14:00',
                'recurring' => true,
            ],
            [
                'start_time' => '14:00',
                'end_time' => '16:00',
                'recurring' => true,
            ],
            [
                'start_time' => '16:00',
                'end_time' => '18:00',
                'recurring' => true,
            ],
        ];

        foreach ($timeslots as $timeslot) {
            TimeSlot::create($timeslot);
        }
    }
}
