@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-xl space-y-4 p-6">
        <h1 class="text-3xl font-bold">✅ Reservatie gelukt!</h1>

        <div class="rounded-lg border border-black p-4">
            <p><span class="font-semibold">Reservatiecode:</span> {{ $reservation->public_code }}</p>
            <p><span class="font-semibold">Datum:</span> {{ $reservation->date->format('d/m/Y') }}</p>
            <p>
                <span class="font-semibold">Tijdslot:</span>
                {{ $reservation->timeSlot->label ?? '—' }}
            </p>
            <p><span class="font-semibold">Aantal bezoekers:</span> {{ $reservation->visitors->count() }}</p>
        </div>

        <div class="rounded-lg border border-black p-4">
            <h2 class="text-xl font-semibold">Bezoekers</h2>
            <ul class="list-disc pl-5">
                @foreach($reservation->visitors as $v)
                    <li>
                        {{ $v->firstname }} {{ $v->lastname }}
                        @if($v->subscription_nr)
                            <span class="text-sm text-gray-600">(abo: {{ $v->subscription_nr }})</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>

        <a href="/reservation" class="inline-block rounded-md border border-black px-4 py-2 hover:bg-gray-100">
            Nieuwe reservatie
        </a>
    </div>
@endsection
