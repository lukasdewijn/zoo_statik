<?php

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\TimeSlot;
use App\Models\TimeSlotCapacity;
use App\Models\Visitor;

test('returns dates that have at least one timeslot with remaining capacity', function () {
    $slot = TimeSlot::factory()->create();

    $date1 = now()->addDays(1);
    while ($date1->isWeekend()) {
        $date1 = $date1->addDay();
    }
    $date1Str = $date1->toDateString();

    $date2 = $date1->copy()->addDay();
    while ($date2->isWeekend()) {
        $date2 = $date2->addDay();
    }
    $date2Str = $date2->toDateString();

    TimeSlotCapacity::factory()->create([
        'date' => $date1Str,
        'time_slot_id' => $slot->id,
        'capacity' => 100,
    ]);

    TimeSlotCapacity::factory()->create([
        'date' => $date2Str,
        'time_slot_id' => $slot->id,
        'capacity' => 100,
    ]);

    $available = TimeSlotCapacity::availableDates();

    expect($available)->toContain($date1Str);
    expect($available)->toContain($date2Str);
});

test('excludes dates where all timeslots are fully booked', function () {
    $slot = TimeSlot::factory()->create();

    $date = now()->addDay();
    while ($date->isWeekend()) {
        $date = $date->addDay();
    }
    $dateStr = $date->toDateString();

    TimeSlotCapacity::factory()->create([
        'date' => $dateStr,
        'time_slot_id' => $slot->id,
        'capacity' => 2,
    ]);

    $reservation = Reservation::factory()->create([
        'date' => $dateStr,
        'time_slot_id' => $slot->id,
    ]);
    Visitor::factory()->count(2)->create(['reservation_id' => $reservation->id]);

    $available = TimeSlotCapacity::availableDates();

    expect($available)->not->toContain($dateStr);
});

test('returns empty array when no capacities exist', function () {
    expect(TimeSlotCapacity::availableDates())->toBe([]);
});

test('includes dates with custom (non-recurring) timeslots that have capacity', function () {
    $customSlot = TimeSlot::factory()->custom()->create();

    $date = now()->addDay();
    while ($date->isWeekend()) {
        $date = $date->addDay();
    }
    $dateStr = $date->toDateString();

    TimeSlotCapacity::factory()->create([
        'date' => $dateStr,
        'time_slot_id' => $customSlot->id,
        'capacity' => 100,
    ]);

    $available = TimeSlotCapacity::availableDates();

    expect($available)->toContain($dateStr);
});

test('includes date if at least one of multiple slots has remaining capacity', function () {
    $slot1 = TimeSlot::factory()->create(['start_time' => '09:00', 'end_time' => '12:00']);
    $slot2 = TimeSlot::factory()->create(['start_time' => '13:00', 'end_time' => '16:00']);

    $date = now()->addDay();
    while ($date->isWeekend()) {
        $date = $date->addDay();
    }
    $dateStr = $date->toDateString();

    // Slot 1 is full
    TimeSlotCapacity::factory()->create([
        'date' => $dateStr,
        'time_slot_id' => $slot1->id,
        'capacity' => 1,
    ]);
    $reservation = Reservation::factory()->create([
        'date' => $dateStr,
        'time_slot_id' => $slot1->id,
    ]);
    Visitor::factory()->create(['reservation_id' => $reservation->id]);

    // Slot 2 has capacity
    TimeSlotCapacity::factory()->create([
        'date' => $dateStr,
        'time_slot_id' => $slot2->id,
        'capacity' => 100,
    ]);

    $available = TimeSlotCapacity::availableDates();

    expect($available)->toContain($dateStr);
});
