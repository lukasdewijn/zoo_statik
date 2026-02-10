<?php

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\TimeSlot;
use App\Models\TimeSlotCapacity;
use App\Models\Visitor;

test('returns availability for a given date', function () {
    $slot = TimeSlot::factory()->create();
    $date = now()->addDay()->toDateString();

    TimeSlotCapacity::factory()->create([
        'date' => $date,
        'time_slot_id' => $slot->id,
        'capacity' => 100,
    ]);

    $this->getJson("/api/v1/availability?date={$date}")
        ->assertOk()
        ->assertJsonPath('meta.date', $date)
        ->assertJsonPath('data.0.capacity', 100)
        ->assertJsonPath('data.0.used', 0)
        ->assertJsonPath('data.0.available', 100)
        ->assertJsonPath('data.0.full', false);
});

test('counts confirmed visitors as used', function () {
    $slot = TimeSlot::factory()->create();
    $date = now()->addDay()->toDateString();

    TimeSlotCapacity::factory()->create([
        'date' => $date,
        'time_slot_id' => $slot->id,
        'capacity' => 100,
    ]);

    $reservation = Reservation::factory()->create([
        'date' => $date,
        'time_slot_id' => $slot->id,
        'status' => ReservationStatus::Confirmed,
    ]);
    Visitor::factory()->count(3)->create(['reservation_id' => $reservation->id]);

    $this->getJson("/api/v1/availability?date={$date}")
        ->assertOk()
        ->assertJsonPath('data.0.used', 3)
        ->assertJsonPath('data.0.available', 97);
});

test('does not count cancelled visitors', function () {
    $slot = TimeSlot::factory()->create();
    $date = now()->addDay()->toDateString();

    TimeSlotCapacity::factory()->create([
        'date' => $date,
        'time_slot_id' => $slot->id,
        'capacity' => 100,
    ]);

    $cancelled = Reservation::factory()->cancelled()->create([
        'date' => $date,
        'time_slot_id' => $slot->id,
    ]);
    Visitor::factory()->count(5)->create(['reservation_id' => $cancelled->id]);

    $this->getJson("/api/v1/availability?date={$date}")
        ->assertOk()
        ->assertJsonPath('data.0.used', 0)
        ->assertJsonPath('data.0.available', 100);
});

test('marks slot as full when capacity is reached', function () {
    $slot = TimeSlot::factory()->create();
    $date = now()->addDay()->toDateString();

    TimeSlotCapacity::factory()->create([
        'date' => $date,
        'time_slot_id' => $slot->id,
        'capacity' => 2,
    ]);

    $reservation = Reservation::factory()->create([
        'date' => $date,
        'time_slot_id' => $slot->id,
    ]);
    Visitor::factory()->count(2)->create(['reservation_id' => $reservation->id]);

    $this->getJson("/api/v1/availability?date={$date}")
        ->assertOk()
        ->assertJsonPath('data.0.full', true)
        ->assertJsonPath('data.0.available', 0);
});

test('includes custom (non-recurring) timeslots with capacity', function () {
    $custom = TimeSlot::factory()->custom()->create();
    $date = now()->addDay()->toDateString();

    TimeSlotCapacity::factory()->create([
        'date' => $date,
        'time_slot_id' => $custom->id,
        'capacity' => 50,
    ]);

    $this->getJson("/api/v1/availability?date={$date}")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.capacity', 50);
});

test('defaults to today when no date is provided', function () {
    $slot = TimeSlot::factory()->create();

    TimeSlotCapacity::factory()->create([
        'date' => now()->toDateString(),
        'time_slot_id' => $slot->id,
    ]);

    $this->getJson('/api/v1/availability')
        ->assertOk()
        ->assertJsonPath('meta.date', now()->toDateString());
});

test('validates date format', function () {
    $this->getJson('/api/v1/availability?date=not-a-date')
        ->assertStatus(422);
});
