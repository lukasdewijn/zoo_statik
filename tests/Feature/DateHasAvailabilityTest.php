<?php

use App\Models\Reservation;
use App\Models\TimeSlot;
use App\Models\TimeSlotCapacity;
use App\Models\Visitor;
use App\Rules\DateHasAvailability;

function passesDateRule(mixed $value): bool
{
    $passed = true;
    (new DateHasAvailability)->validate('field', $value, function () use (&$passed) {
        $passed = false;
    });

    return $passed;
}

test('passes when date has a timeslot with remaining capacity', function () {
    $slot = TimeSlot::factory()->create();

    $date = now()->addDay();
    while ($date->isWeekend()) {
        $date = $date->addDay();
    }

    TimeSlotCapacity::factory()->create([
        'date' => $date->toDateString(),
        'time_slot_id' => $slot->id,
        'capacity' => 100,
    ]);

    expect(passesDateRule($date->toDateString()))->toBeTrue();
});

test('fails when date has no timeslot capacity records', function () {
    expect(passesDateRule(now()->addDays(30)->toDateString()))->toBeFalse();
});

test('fails when all timeslots are fully booked', function () {
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

    expect(passesDateRule($dateStr))->toBeFalse();
});

test('skips validation for invalid date formats', function () {
    // Invalid format should pass (let the 'date' rule handle it)
    expect(passesDateRule('not-a-date'))->toBeTrue();
});

test('includes custom (non-recurring) timeslots with capacity', function () {
    $customSlot = TimeSlot::factory()->custom()->create();

    $date = now()->addDay();
    while ($date->isWeekend()) {
        $date = $date->addDay();
    }

    TimeSlotCapacity::factory()->create([
        'date' => $date->toDateString(),
        'time_slot_id' => $customSlot->id,
        'capacity' => 100,
    ]);

    expect(passesDateRule($date->toDateString()))->toBeTrue();
});
