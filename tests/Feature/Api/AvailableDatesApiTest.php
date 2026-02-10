<?php

use App\Models\Reservation;
use App\Models\TimeSlot;
use App\Models\TimeSlotCapacity;
use App\Models\Visitor;

test('returns available dates', function () {
    $slot = TimeSlot::factory()->create();

    $date = now()->addDay();
    while ($date->isWeekend()) {
        $date = $date->addDay();
    }
    $dateStr = $date->toDateString();

    TimeSlotCapacity::factory()->create([
        'date' => $dateStr,
        'time_slot_id' => $slot->id,
        'capacity' => 100,
    ]);

    $this->getJson('/api/v1/available-dates')
        ->assertOk()
        ->assertJsonFragment([$dateStr]);
});

test('excludes fully booked dates', function () {
    $slot = TimeSlot::factory()->create();

    $date = now()->addDay();
    while ($date->isWeekend()) {
        $date = $date->addDay();
    }
    $dateStr = $date->toDateString();

    TimeSlotCapacity::factory()->create([
        'date' => $dateStr,
        'time_slot_id' => $slot->id,
        'capacity' => 1,
    ]);

    $reservation = Reservation::factory()->create([
        'date' => $dateStr,
        'time_slot_id' => $slot->id,
    ]);
    Visitor::factory()->create(['reservation_id' => $reservation->id]);

    $this->getJson('/api/v1/available-dates')
        ->assertOk()
        ->assertJsonMissing([$dateStr]);
});

test('returns empty array when no capacities exist', function () {
    $this->getJson('/api/v1/available-dates')
        ->assertOk()
        ->assertJsonPath('data', []);
});