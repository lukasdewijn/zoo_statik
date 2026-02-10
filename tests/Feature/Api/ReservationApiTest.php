<?php

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\TimeSlot;
use App\Models\TimeSlotCapacity;
use App\Models\Visitor;
use App\Notifications\ReservationConfirmed;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->slot = TimeSlot::factory()->create();

    // Find next weekday to guarantee a valid test date
    $date = now()->addDay();
    while ($date->isWeekend()) {
        $date = $date->addDay();
    }
    $this->testDate = $date->toDateString();

    // Create a TimeSlotCapacity so DateHasAvailability passes
    TimeSlotCapacity::factory()->create([
        'date' => $this->testDate,
        'time_slot_id' => $this->slot->id,
        'capacity' => TimeSlotCapacity::DEFAULT_CAPACITY,
    ]);
});

function validPayload(array $overrides = []): array
{
    // Use the testDate from the test context
    $date = test()->testDate;

    return array_merge([
        'date' => $date,
        'time_slot_id' => TimeSlot::first()->id,
        'contact_email' => 'test@example.com',
        'visitors' => [
            ['first_name' => 'Jan', 'last_name' => 'Janssen'],
        ],
    ], $overrides);
}

// --- POST /api/v1/reservations ---

test('can create a reservation', function () {
    Notification::fake();

    $response = $this->postJson('/api/v1/reservations', validPayload());

    $response->assertStatus(201)
        ->assertJsonPath('data.date', $this->testDate)
        ->assertJsonPath('data.timeslot.label', $this->slot->label)
        ->assertJsonCount(1, 'data.visitors');

    $this->assertDatabaseHas('reservations', [
        'time_slot_id' => $this->slot->id,
        'status' => 'confirmed',
    ]);

    $this->assertDatabaseHas('visitors', [
        'first_name' => 'Jan',
        'last_name' => 'Janssen',
    ]);
});

test('reservation generates a uuid public_code', function () {
    Notification::fake();

    $response = $this->postJson('/api/v1/reservations', validPayload());

    $response->assertStatus(201);
    $publicCode = $response->json('data.public_code');
    expect($publicCode)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
});

test('sends confirmation email after creating reservation', function () {
    Notification::fake();

    $this->postJson('/api/v1/reservations', validPayload());

    Notification::assertSentOnDemand(ReservationConfirmed::class);
});

test('can create reservation with multiple visitors', function () {
    Notification::fake();

    $response = $this->postJson('/api/v1/reservations', validPayload([
        'visitors' => [
            ['first_name' => 'Jan', 'last_name' => 'Janssen'],
            ['first_name' => 'Piet', 'last_name' => 'Pietersen'],
            ['first_name' => 'Klaas', 'last_name' => 'Kansen'],
        ],
    ]));

    $response->assertStatus(201)
        ->assertJsonCount(3, 'data.visitors');
});

test('validates required fields', function () {
    $this->postJson('/api/v1/reservations', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['date', 'time_slot_id', 'visitors', 'contact_email']);
});

test('validates date is not in the past', function () {
    $this->postJson('/api/v1/reservations', validPayload([
        'date' => now()->subDay()->toDateString(),
    ]))->assertStatus(422)
        ->assertJsonValidationErrors(['date']);
});

test('validates time_slot_id exists', function () {
    $this->postJson('/api/v1/reservations', validPayload([
        'time_slot_id' => 9999,
    ]))->assertStatus(422)
        ->assertJsonValidationErrors(['time_slot_id']);
});

test('validates visitor first_name and last_name are required', function () {
    $this->postJson('/api/v1/reservations', validPayload([
        'visitors' => [
            ['first_name' => '', 'last_name' => ''],
        ],
    ]))->assertStatus(422)
        ->assertJsonValidationErrors(['visitors.0.first_name', 'visitors.0.last_name']);
});

test('validates contact_email format', function () {
    $this->postJson('/api/v1/reservations', validPayload([
        'contact_email' => 'not-an-email',
    ]))->assertStatus(422)
        ->assertJsonValidationErrors(['contact_email']);
});

test('rejects reservation when timeslot is full', function () {
    Notification::fake();

    // Set the main slot capacity to 2
    TimeSlotCapacity::query()
        ->whereDate('date', $this->testDate)
        ->where('time_slot_id', $this->slot->id)
        ->update(['capacity' => 2]);

    // Add a second slot with availability so the date itself passes DateHasAvailability
    $otherSlot = TimeSlot::factory()->create(['start_time' => '13:00', 'end_time' => '16:00']);
    TimeSlotCapacity::factory()->create([
        'date' => $this->testDate,
        'time_slot_id' => $otherSlot->id,
        'capacity' => 100,
    ]);

    $reservation = Reservation::factory()->create([
        'date' => $this->testDate,
        'time_slot_id' => $this->slot->id,
    ]);
    Visitor::factory()->count(2)->create(['reservation_id' => $reservation->id]);

    $this->postJson('/api/v1/reservations', validPayload())
        ->assertStatus(422)
        ->assertJsonValidationErrors(['time_slot_id']);
});

test('rejects reservation when adding visitors would exceed capacity', function () {
    Notification::fake();

    TimeSlotCapacity::query()
        ->whereDate('date', $this->testDate)
        ->where('time_slot_id', $this->slot->id)
        ->update(['capacity' => 3]);

    $reservation = Reservation::factory()->create([
        'date' => $this->testDate,
        'time_slot_id' => $this->slot->id,
    ]);
    Visitor::factory()->count(2)->create(['reservation_id' => $reservation->id]);

    $this->postJson('/api/v1/reservations', validPayload([
        'visitors' => [
            ['first_name' => 'A', 'last_name' => 'B'],
            ['first_name' => 'C', 'last_name' => 'D'],
        ],
    ]))->assertStatus(422)
        ->assertJsonValidationErrors(['time_slot_id']);
});

test('rejects reservation when date has no availability at all', function () {
    Notification::fake();

    // Set capacity to 1 and fill it
    TimeSlotCapacity::query()
        ->whereDate('date', $this->testDate)
        ->where('time_slot_id', $this->slot->id)
        ->update(['capacity' => 1]);

    $reservation = Reservation::factory()->create([
        'date' => $this->testDate,
        'time_slot_id' => $this->slot->id,
    ]);
    Visitor::factory()->create(['reservation_id' => $reservation->id]);

    $this->postJson('/api/v1/reservations', validPayload())
        ->assertStatus(422)
        ->assertJsonValidationErrors(['date']);
});

test('cancelled reservations do not count toward capacity', function () {
    Notification::fake();

    TimeSlotCapacity::where('date', $this->testDate)
        ->where('time_slot_id', $this->slot->id)
        ->update(['capacity' => 2]);

    $cancelled = Reservation::factory()->cancelled()->create([
        'date' => $this->testDate,
        'time_slot_id' => $this->slot->id,
    ]);
    Visitor::factory()->count(2)->create(['reservation_id' => $cancelled->id]);

    $response = $this->postJson('/api/v1/reservations', validPayload());

    $response->assertStatus(201);
});

test('rejects reservation when date has no availability', function () {
    // Use a date that has no TimeSlotCapacity records
    $date = now()->addDays(30);
    while ($date->isWeekend()) {
        $date = $date->addDay();
    }
    $emptyDate = $date->toDateString();

    $this->postJson('/api/v1/reservations', validPayload([
        'date' => $emptyDate,
    ]))->assertStatus(422)
        ->assertJsonValidationErrors(['date']);
});

// --- GET /api/v1/reservations/{public_code} ---

test('can retrieve a reservation by public_code', function () {
    $reservation = Reservation::factory()->create([
        'time_slot_id' => $this->slot->id,
    ]);
    Visitor::factory()->create([
        'reservation_id' => $reservation->id,
        'first_name' => 'Test',
        'last_name' => 'User',
    ]);

    $this->getJson("/api/v1/reservations/{$reservation->public_code}")
        ->assertOk()
        ->assertJsonPath('data.public_code', $reservation->public_code)
        ->assertJsonPath('data.visitors.0.first_name', 'Test')
        ->assertJsonPath('data.visitors.0.last_name', 'User');
});

test('returns 404 for unknown public_code', function () {
    $this->getJson('/api/v1/reservations/non-existent-code')
        ->assertNotFound();
});

// --- POST /api/v1/reservations/{public_code}/cancel ---

test('can cancel a reservation with correct email', function () {
    $reservation = Reservation::factory()->create([
        'time_slot_id' => $this->slot->id,
        'contact_email' => 'test@example.com',
    ]);

    $this->postJson("/api/v1/reservations/{$reservation->public_code}/cancel", [
        'contact_email' => 'test@example.com',
    ])->assertOk()
        ->assertJsonPath('data.status', 'cancelled');

    $this->assertDatabaseHas('reservations', [
        'id' => $reservation->id,
        'status' => 'cancelled',
    ]);
});

test('cancel requires contact_email', function () {
    $reservation = Reservation::factory()->create([
        'time_slot_id' => $this->slot->id,
    ]);

    $this->postJson("/api/v1/reservations/{$reservation->public_code}/cancel", [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['contact_email']);
});

test('cancel rejects wrong email', function () {
    $reservation = Reservation::factory()->create([
        'time_slot_id' => $this->slot->id,
        'contact_email' => 'real@example.com',
    ]);

    $this->postJson("/api/v1/reservations/{$reservation->public_code}/cancel", [
        'contact_email' => 'wrong@example.com',
    ])->assertStatus(403);
});

test('cancel returns 409 if already cancelled', function () {
    $reservation = Reservation::factory()->cancelled()->create([
        'time_slot_id' => $this->slot->id,
        'contact_email' => 'test@example.com',
    ]);

    $this->postJson("/api/v1/reservations/{$reservation->public_code}/cancel", [
        'contact_email' => 'test@example.com',
    ])->assertStatus(409);
});

test('cancel returns 404 for unknown public_code', function () {
    $this->postJson('/api/v1/reservations/non-existent-code/cancel', [
        'contact_email' => 'test@example.com',
    ])->assertNotFound();
});
