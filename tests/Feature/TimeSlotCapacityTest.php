<?php

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\TimeSlot;
use App\Models\TimeSlotCapacity;
use App\Models\Visitor;

beforeEach(function () {
    $this->slot = TimeSlot::factory()->create();
    $this->date = now()->addDay()->toDateString();
});

test('capacityFor returns capacity from database', function () {
    TimeSlotCapacity::factory()->create([
        'date' => $this->date,
        'time_slot_id' => $this->slot->id,
        'capacity' => 150,
    ]);

    expect(TimeSlotCapacity::capacityFor($this->date, $this->slot->id))->toBe(150);
});

test('capacityFor returns zero when no record exists', function () {
    expect(TimeSlotCapacity::capacityFor($this->date, $this->slot->id))
        ->toBe(0);
});

test('reservedCount counts visitors of confirmed reservations', function () {
    $reservation = Reservation::factory()->create([
        'date' => $this->date,
        'time_slot_id' => $this->slot->id,
        'status' => ReservationStatus::Confirmed,
    ]);
    Visitor::factory()->count(5)->create(['reservation_id' => $reservation->id]);

    expect(TimeSlotCapacity::reservedCount($this->date, $this->slot->id))->toBe(5);
});

test('reservedCount ignores cancelled reservations', function () {
    $cancelled = Reservation::factory()->cancelled()->create([
        'date' => $this->date,
        'time_slot_id' => $this->slot->id,
    ]);
    Visitor::factory()->count(3)->create(['reservation_id' => $cancelled->id]);

    expect(TimeSlotCapacity::reservedCount($this->date, $this->slot->id))->toBe(0);
});

test('reservedCount returns zero when no reservations exist', function () {
    expect(TimeSlotCapacity::reservedCount($this->date, $this->slot->id))->toBe(0);
});

test('remainingCapacity calculates correctly', function () {
    TimeSlotCapacity::factory()->create([
        'date' => $this->date,
        'time_slot_id' => $this->slot->id,
        'capacity' => 100,
    ]);

    $reservation = Reservation::factory()->create([
        'date' => $this->date,
        'time_slot_id' => $this->slot->id,
    ]);
    Visitor::factory()->count(30)->create(['reservation_id' => $reservation->id]);

    expect(TimeSlotCapacity::remainingCapacity($this->date, $this->slot->id))->toBe(70);
});

test('remainingCapacity never goes below zero', function () {
    TimeSlotCapacity::factory()->create([
        'date' => $this->date,
        'time_slot_id' => $this->slot->id,
        'capacity' => 2,
    ]);

    $reservation = Reservation::factory()->create([
        'date' => $this->date,
        'time_slot_id' => $this->slot->id,
    ]);
    Visitor::factory()->count(5)->create(['reservation_id' => $reservation->id]);

    expect(TimeSlotCapacity::remainingCapacity($this->date, $this->slot->id))->toBe(0);
});
