<?php

use App\Models\TimeSlot;
use App\Models\TimeSlotCapacity;

test('generates capacity rows for all timeslots (weekdays only)', function () {
    TimeSlot::factory()->count(2)->create();

    // 2026-06-01 is a Monday → Mon, Tue, Wed = 3 weekdays
    $this->artisan('timeslots:generate-capacities', ['--days' => 3, '--start' => '2026-06-01'])
        ->assertSuccessful();

    expect(TimeSlotCapacity::count())->toBe(6); // 2 slots x 3 weekdays
});

test('generates capacity rows including weekends when flag is set', function () {
    TimeSlot::factory()->count(1)->create();

    // 2026-06-05 is a Friday → Fri, Sat, Sun = 3 days, only 1 weekday without flag
    $this->artisan('timeslots:generate-capacities', [
        '--days' => 3,
        '--start' => '2026-06-05',
        '--include-weekends' => true,
    ])->assertSuccessful();

    expect(TimeSlotCapacity::count())->toBe(3); // 1 slot x 3 days (incl weekends)
});

test('skips weekends by default', function () {
    TimeSlot::factory()->count(1)->create();

    // 2026-06-05 is a Friday → Fri, Sat, Sun = only Fri is a weekday
    $this->artisan('timeslots:generate-capacities', [
        '--days' => 3,
        '--start' => '2026-06-05',
    ])->assertSuccessful();

    expect(TimeSlotCapacity::count())->toBe(1); // only Friday

    $dates = TimeSlotCapacity::pluck('date')
        ->map(fn ($d) => $d->toDateString())
        ->all();

    expect($dates)->toBe(['2026-06-05']);
});

test('uses default capacity value', function () {
    TimeSlot::factory()->create();

    // Use a known weekday
    $this->artisan('timeslots:generate-capacities', ['--days' => 1, '--start' => '2026-06-01'])
        ->assertSuccessful();

    expect(TimeSlotCapacity::first()->capacity)->toBe(TimeSlotCapacity::DEFAULT_CAPACITY);
});

test('fails when no timeslots exist', function () {
    $this->artisan('timeslots:generate-capacities')
        ->assertFailed();
});

test('accepts custom start date', function () {
    TimeSlot::factory()->create();

    // 2026-06-01 (Mon) and 2026-06-02 (Tue) - both weekdays
    $this->artisan('timeslots:generate-capacities', [
        '--start' => '2026-06-01',
        '--days' => 2,
    ])->assertSuccessful();

    $dates = TimeSlotCapacity::pluck('date')
        ->map(fn ($d) => $d->toDateString())
        ->sort()
        ->values()
        ->all();

    expect($dates)->toBe(['2026-06-01', '2026-06-02']);
});

test('upsert does not duplicate existing rows', function () {
    $slot = TimeSlot::factory()->create();

    // Use a known weekday
    $this->artisan('timeslots:generate-capacities', ['--days' => 2, '--start' => '2026-06-01'])
        ->assertSuccessful();

    $this->artisan('timeslots:generate-capacities', ['--days' => 2, '--start' => '2026-06-01'])
        ->assertSuccessful();

    expect(TimeSlotCapacity::count())->toBe(2);
});
