<?php

use App\Models\TimeSlot;

test('returns all time slots', function () {
    TimeSlot::factory()->create(['start_time' => '09:00', 'end_time' => '12:00']);
    TimeSlot::factory()->create(['start_time' => '13:00', 'end_time' => '17:00']);

    $this->getJson('/api/v1/time-slots')
        ->assertOk()
        ->assertJsonCount(2)
        ->assertJsonPath('0.label', '09:00 - 12:00')
        ->assertJsonPath('1.label', '13:00 - 17:00');
});

test('includes custom (non-recurring) time slots', function () {
    TimeSlot::factory()->create(['start_time' => '09:00', 'end_time' => '12:00']);
    TimeSlot::factory()->custom()->create(['start_time' => '13:00', 'end_time' => '17:00']);

    $this->getJson('/api/v1/time-slots')
        ->assertOk()
        ->assertJsonCount(2);
});

test('returns empty array when no time slots exist', function () {
    $this->getJson('/api/v1/time-slots')
        ->assertOk()
        ->assertJsonCount(0);
});
