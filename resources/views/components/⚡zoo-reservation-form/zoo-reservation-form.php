<?php


use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\TimeSlot;
use App\Models\Visitor;
use App\Models\Reservation;

use Illuminate\Support\Facades\Http;


new class extends Component
{

    public $date;
    public $timeslot_id;
    public $timeslots = [];

    public array $visitors = [];

    public int $capacity = 200;
    public ?int $remaining = null;//null = nog niet gekozen

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
        if(count($this->visitors) >= 200) {
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
    private function checkAboNummer(string $abonr){
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

            // validate each visitor
            'visitors' => ['required','array', 'min:1', 'max:200'],
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
            if(! $this->checkAboNummer($visitor['abonr'])){
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
            if ($newCount + $currentCount > 200) {
                // gooi exception zodat transaction netjes rollbackt
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'timeslot_id' => 'Dit tijdslot is volzet (max 200 bezoekers).',
                ]);
            }

            $reservation = Reservation::create([
                'public_code' => (string) Str::uuid(),
                'date' => $validated['date'],
                'timeslot_id' => $validated['timeslot_id'],
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


        //form reset + message
        $this->reset(['date','timeslot_id']);
        $this->visitors = [$this->emptyVisitors()];

        session()->flash('success', 'Reservation succesvol toegevoegd.');

        return redirect()->route('reservations.success', $reservation->public_code);
    }

};
