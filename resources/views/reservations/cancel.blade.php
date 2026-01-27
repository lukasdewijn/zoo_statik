@extends('layouts.app')

@section('content')
    <div class="max-w-xl mx-auto p-6">
        @if (session('success'))
            <div class="text-green-600 mb-4">{{ session('success') }}</div>
        @endif

        <h1 class="text-2xl font-bold mb-4">Reservatie annuleren</h1>

        <div class="border p-4 rounded mb-4">
            <div><b>Code:</b> {{ $reservation->public_code }}</div>
            <div><b>Datum:</b> {{ $reservation->date->format('d/m/Y') }}</div>
            <div><b>Tijdslot:</b> {{ $reservation->timeslot_id }}</div>
            <div><b>Status:</b> {{ $reservation->status }}</div>
        </div>

        @if ($reservation->status !== 'cancelled')
                <form method="POST" action="{{ route('reservations.cancel.do', $reservation->public_code) }}">
                    @csrf
                    <button class="px-4 py-2 bg-red-600 text-white rounded">
                        Bevestig annuleren
                    </button>
                </form>
        @endif
    </div>
@endsection
