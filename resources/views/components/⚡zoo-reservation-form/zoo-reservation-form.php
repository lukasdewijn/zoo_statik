<?php


use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\TimeSlot;
use App\Models\Visitor;
use App\Models\Reservation;

use Illuminate\Support\Facades\Notification;
use App\Notifications\ReservationConfirmed;


/**
 * @property bool $canAddVisitor
 * @property bool $canSubmit
 * @property bool $isSoldOut
 * @property bool $showRemaining
 */


new class extends Component
{
    private const CAPACITY = 200;
    public ? string $date = null;
    public ?int $timeslot_id = null;
    public $timeslots = [];
    public array $visitors = [];
    public int $capacity = self::CAPACITY;
    public ?int $remaining = null;
    public string $contact_email = '';
    public function mount()
    {
        $this->timeslots = TimeSlot::where('active',true)
            ->orderBy('start_time')
            ->get();

        $this->visitors = [
            $this->emptyVisitors(),
        ];
    }

    private function emptyVisitors(): array
    {
        return [
            'key' => (string)Str::uuid(), // stable key for Livewire DOM diffing
            'voornaam' => '',
            'achternaam' => '',
            'abonr' => ''
        ];
    }

    public function addVisitor()
    {
        $this->refreshRemaining();
        if($this->remaining !== null && $this->remaining <= count($this->visitors)) {
            // je probeert meer visitors toe te voegen dan er plaatsen zijn
            return;
        }
        if(count($this->visitors) >= self::CAPACITY) {
            return;
        }
        $this->visitors[] = $this->emptyVisitors();
    }

    public function removeVisitor(string $key): void
    {
        if(count($this->visitors)<=1){
            return;
        }

        $this->visitors =array_values(array_filter(
            $this->visitors,
            fn ($v) => $v['key'] != $key
        ));

        $this->refreshRemaining();
    }
    private function isValidAboNumber(string $abonr){
        if($abonr == null){return true;}
        //verwijder alles dat geen cijfer is
        $digits = preg_replace('/\D/', '', $abonr);
        //check of het 10 getallen zijn
        if(strlen($digits) !==10){
            return false;
        }
        //splits de getallen
        $first = (int) substr($digits, 0, 8);
        $second = (int) substr($digits, 8, 2);

        return $first%97 === $second;
    }

    private function currentCountForSelectedSlot(): int
    {
        if(!$this->date || !$this->timeslot_id){
            return 0;
        }
        return Visitor::query()
            ->join('reservations', 'visitors.reservation_id', '=', 'reservations.id')
            ->whereDate('reservations.date', $this->date)
            ->where('reservations.timeslot_id', $this->timeslot_id)
            ->count();
    }
    private function refreshRemaining(): void
    {
        if (!$this->date || !$this->timeslot_id) {
            $this->remaining = null;
            return;
        }

        $current = $this->currentCountForSelectedSlot();
        $this->remaining = max(0, $this->capacity - $current);
    }
    public function updatedDate(): void{$this->refreshRemaining();}
    public function updatedTimeslotId(): void{$this->refreshRemaining();}

    public function save()
    {
        $validated = $this->validate([
            'date' => ['required','date','after_or_equal:today'],
            'timeslot_id' => ['required', 'exists:time_slots,id'],
            'contact_email' => ['required','email', 'max:255'],

            // validate each visitor
            'visitors' => ['required','array', 'min:1', 'max:' . self::CAPACITY],
            'visitors.*.voornaam' => ['required','string', 'max:255'],
            'visitors.*.achternaam' => ['required','string', 'max:255'],
            'visitors.*.abonr' => ['nullable','string', 'max:255'],
        ]);

        $timeslot = TimeSlot::find($this->timeslot_id);

        $startDateTime = Carbon::parse(
            $this->date . ' ' . $timeslot->start_time
        );

        if ($startDateTime->isPast()){
            $this->addError('timeslot_id', 'Too Late Mate');
            return;
        }

        foreach ($this->visitors as $index => $visitor) {
            if(! $this->isValidAboNumber($visitor['abonr'])){
                $this->addError("visitors.$index.abonr", "Ongeldige abonnementsnummer");
                return;
            }
        }

        $reservation = null;

        $reservation = DB::transaction(function () use ($validated) {
            // Lock zodat 2 submits niet tegelijk kunnen tell+inserten
            TimeSlot::whereKey($validated['timeslot_id'])->lockForUpdate()->first();

            $currentCount = Visitor::query()
                ->join('reservations', 'visitors.reservation_id', '=', 'reservations.id')
                ->whereDate('reservations.date', $validated['date'])
                ->where('reservations.timeslot_id', $validated['timeslot_id'])
                ->count();

            $newCount = count($validated['visitors']);
            if ($newCount + $currentCount > self::CAPACITY) {
                // gooi exception zodat transaction netjes rollbackt
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'timeslot_id' => __('zoo.form.errors.timeslot_full', ['capacity' => self::CAPACITY,
                    ]),
                ]);
            }

            $reservation = Reservation::create([
                'date' => $validated['date'],
                'timeslot_id' => $validated['timeslot_id'],
                'contact_email' => $validated['contact_email'],
            ]);

            foreach ($validated['visitors'] as $visitor) {
                Visitor::create([
                    'reservation_id' => $reservation->id,
                    'firstname' => $visitor['voornaam'],
                    'lastname' => $visitor['achternaam'],
                    'subscription_nr' => $visitor['abonr'],
                ]);
            }

            return $reservation; // <<< belangrijk
        });

        Notification::route('mail', $reservation->contact_email)
            ->notify(new ReservationConfirmed($reservation));

        //form reset + message
        $this->reset(['date','timeslot_id', 'contact_email']);
        $this->visitors = [$this->emptyVisitors()];

        session()->flash('success', 'Reservation succesvol toegevoegd.');

        return redirect()->route('reservations.success', $reservation->public_code);
    }

    public function getCanAddVisitorProperty(): bool
    {
        if (is_null($this->remaining)) return true;
        return $this->remaining > 0 && count($this->visitors) < $this->remaining;
    }
    public function getIsSoldOutProperty(): bool
    {
        return $this->remaining === 0;
    }
    public function getCanSubmitProperty(): bool
    {
        if (is_null($this->remaining)) return true;
        return $this->remaining > 0 && count($this->visitors) <= $this->remaining;
    }
    public function getShowRemainingProperty(): bool
    {
        return $this->remaining !== null;
    }
};
