<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TimeSlot;

class TimeSlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timeslots = [
            [
                'label' => '10:00 - 12:00',
                'start_time' => '10:00',
                'end_time' => '12:00',
                'active' => true,
            ],
            [
                'label' => '12:00 - 14:00',
                'start_time' => '12:00',
                'end_time' => '14:00',
                'active' => true,
            ],
            [
                'label' => '14:00 - 16:00',
                'start_time' => '14:00',
                'end_time' => '16:00',
                'active' => true,
            ],
            [
                'label' => '16:00 - 18:00',
                'start_time' => '16:00',
                'end_time' => '18:00',
                'active' => true,
            ],
        ];

        foreach ($timeslots as $timeslot) {
            TimeSlot::create($timeslot);
        }
    }
}
