<?php

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\TimeSlot;
use App\Models\TimeSlotCapacity;
use App\Models\Visitor;
use App\Notifications\ReservationConfirmed;
use App\Services\ReservationService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->service = app(ReservationService::class);
    $this->slot = TimeSlot::factory()->create([
        'start_time' => '09:00',
        'end_time' => '23:59',
    ]);
    $this->tomorrow = now()->addDay()->toDateString();
});

function servicePayload(array $overrides = []): array
{
    $slot = TimeSlot::first();

    return array_merge([
        'date' => now()->addDay()->toDateString(),
        'time_slot_id' => $slot->id,
        'contact_email' => 'test@example.com',
        'visitors' => [
            ['first_name' => 'Jan', 'last_name' => 'Janssen', 'subscription_number' => null],
        ],
    ], $overrides);
}

test('creates reservation with visitors', function () {
    Notification::fake();

    $reservation = $this->service->create(servicePayload([
        'visitors' => [
            ['first_name' => 'A', 'last_name' => 'B', 'subscription_number' => null],
            ['first_name' => 'C', 'last_name' => 'D', 'subscription_number' => null],
        ],
    ]));

    expect($reservation)->toBeInstanceOf(Reservation::class)
        ->and($reservation->status)->toBe(ReservationStatus::Confirmed)
        ->and($reservation->visitors)->toHaveCount(2)
        ->and($reservation->timeSlot)->not->toBeNull();
});

test('creates capacity row if none exists', function () {
    Notification::fake();

    $this->service->create(servicePayload());

    expect(TimeSlotCapacity::where('time_slot_id', $this->slot->id)->exists())->toBeTrue();
});

test('throws when capacity is exceeded', function () {
    Notification::fake();

    TimeSlotCapacity::factory()->create([
        'date' => $this->tomorrow,
        'time_slot_id' => $this->slot->id,
        'capacity' => 1,
    ]);

    $existing = Reservation::factory()->create([
        'date' => $this->tomorrow,
        'time_slot_id' => $this->slot->id,
    ]);
    Visitor::factory()->create(['reservation_id' => $existing->id]);

    $this->service->create(servicePayload());
})->throws(ValidationException::class);

test('throws when timeslot has ended today', function () {
    Notification::fake();

    $pastSlot = TimeSlot::factory()->create([
        'start_time' => '00:00',
        'end_time' => '00:01',
    ]);

    $this->service->create(servicePayload([
        'date' => now()->toDateString(),
        'time_slot_id' => $pastSlot->id,
    ]));
})->throws(ValidationException::class);

test('sends notification after creation', function () {
    Notification::fake();

    $this->service->create(servicePayload());

    Notification::assertSentOnDemand(ReservationConfirmed::class);
});

test('does not send notification when no contact_email', function () {
    Notification::fake();

    $this->service->create(servicePayload([
        'contact_email' => null,
    ]));

    Notification::assertNothingSent();
});
