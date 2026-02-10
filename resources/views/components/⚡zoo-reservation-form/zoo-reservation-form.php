<?php

use App\Models\TimeSlot;
use App\Models\TimeSlotCapacity;
use App\Rules\DateHasAvailability;
use App\Rules\SubscriptionNumber;
use App\Services\ReservationService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * @property bool $canAddVisitor
 * @property bool $canSubmit
 * @property bool $isSoldOut
 * @property bool $showRemaining
 */
new class extends Component
{
    private const CAPACITY = TimeSlotCapacity::DEFAULT_CAPACITY;

    public ?string $date = null;

    public ?int $time_slot_id = null;

    public $timeslots = [];

    public array $timeslotAvailability = [];

    public array $availableDates = [];

    public array $visitors = [];

    public int $capacity = self::CAPACITY;

    public ?int $remaining = null;

    public string $contact_email = '';

    public ?string $capybaraUrl = null;

    public ?string $capybaraAlt = null;

    public ?string $capybaraFact = null;

    public function mount()
    {
        $this->timeslots = TimeSlot::orderBy('start_time')->get();

        $this->availableDates = TimeSlotCapacity::availableDates();

        $this->visitors = [
            $this->emptyVisitors(),
        ];

        $this->loadCapybara();
    }

    private function loadCapybara(): void
    {
        try {
            $imageResponse = Http::timeout(3)->get('https://api.capy.lol/v1/capybara?json=true');
            $factResponse = Http::timeout(3)->get('https://api.capy.lol/v1/fact');

            if ($imageResponse->successful()) {
                $this->capybaraUrl = $imageResponse->json('data.url');
                $this->capybaraAlt = $imageResponse->json('data.alt') ?? 'A capybara';
            }

            if ($factResponse->successful()) {
                $this->capybaraFact = $factResponse->json('data.fact');
            }
        } catch (\Exception $e) {
            // API is down, no capybara today
        }
    }

    private function emptyVisitors(): array
    {
        return [
            'key' => (string) Str::uuid(), // stable key for Livewire DOM diffing
            'voornaam' => '',
            'achternaam' => '',
            'abonr' => '',
        ];
    }

    public function addVisitor()
    {
        $this->refreshRemaining();
        if ($this->remaining !== null && $this->remaining <= count($this->visitors)) {
            // No more spots available
            return;
        }
        if (count($this->visitors) >= self::CAPACITY) {
            return;
        }
        $this->visitors[] = $this->emptyVisitors();
    }

    public function removeVisitor(string $key): void
    {
        if (count($this->visitors) <= 1) {
            return;
        }

        $this->visitors = array_values(array_filter(
            $this->visitors,
            fn ($v) => ($v['key'] ?? '') != $key
        ));

        $this->refreshRemaining();
    }

    private function currentCountForSelectedSlot(): int
    {
        if (! $this->date || ! $this->time_slot_id) {
            return 0;
        }

        return TimeSlotCapacity::reservedCount($this->date, $this->time_slot_id);
    }

    private function refreshRemaining(): void
    {
        if (! $this->date || ! $this->time_slot_id) {
            $this->remaining = null;

            return;
        }

        $this->capacity = $this->capacityForSelectedSlot($this->date, $this->time_slot_id);
        $current = $this->currentCountForSelectedSlot();
        $this->remaining = max(0, $this->capacity - $current);
    }

    public function updatedDate(): void
    {
        $this->time_slot_id = null;
        $this->remaining = null;
        $this->timeslotAvailability = [];

        if ($this->date) {
            foreach ($this->timeslots as $timeslot) {
                if (TimeSlotCapacity::hasCapacityRecord($this->date, $timeslot->id)) {
                    $this->timeslotAvailability[$timeslot->id] = TimeSlotCapacity::remainingCapacity($this->date, $timeslot->id);
                }
            }
        }
    }

    public function updatedTimeSlotId(): void
    {
        $this->refreshRemaining();
    }

    public function save(ReservationService $service)
    {
        $validated = $this->validate([
            'date' => ['required', 'date', 'after_or_equal:today', new DateHasAvailability],
            'time_slot_id' => ['required', 'exists:time_slots,id'],
            'contact_email' => ['required', 'email', 'max:255'],
            'visitors' => ['required', 'array', 'min:1', 'max:'.self::CAPACITY],
            'visitors.*.voornaam' => ['required', 'string', 'max:255'],
            'visitors.*.achternaam' => ['required', 'string', 'max:255'],
            'visitors.*.abonr' => ['nullable', new SubscriptionNumber],
        ]);

        $reservation = $service->create([
            'date' => $validated['date'],
            'time_slot_id' => $validated['time_slot_id'],
            'contact_email' => $validated['contact_email'],
            'visitors' => array_map(fn ($v) => [
                'first_name' => $v['voornaam'],
                'last_name' => $v['achternaam'],
                'subscription_number' => $v['abonr'] ?? null,
            ], $validated['visitors']),
        ]);

        // Reset form
        $this->reset(['date', 'time_slot_id', 'contact_email']);
        $this->timeslotAvailability = [];
        $this->visitors = [$this->emptyVisitors()];

        session()->flash('success', 'Reservation succesvol toegevoegd.');

        return redirect()->route('reservations.success', $reservation->public_code);
    }

    private function capacityForSelectedSlot(?string $date = null, ?int $timeslotId = null): int
    {
        $date = $date ?? $this->date;
        $timeslotId = $timeslotId ?? $this->time_slot_id;

        if (! $date || ! $timeslotId) {
            return self::CAPACITY;
        }

        return TimeSlotCapacity::capacityFor($date, $timeslotId);
    }

    public function getCanAddVisitorProperty(): bool
    {
        if (is_null($this->remaining)) {
            return true;
        }

        return $this->remaining > 0 && count($this->visitors) < $this->remaining;
    }

    public function getIsSoldOutProperty(): bool
    {
        return $this->remaining === 0;
    }

    public function getCanSubmitProperty(): bool
    {
        if (is_null($this->remaining)) {
            return true;
        }

        return $this->remaining > 0 && count($this->visitors) <= $this->remaining;
    }

    public function getShowRemainingProperty(): bool
    {
        return $this->remaining !== null;
    }
};
