@extends('layouts.app')

@section('content')
    <div class="max-w-xl mx-auto p-6">
        @if (session('success'))
            <div class="text-green-600 mb-4">{{ session('success') }}</div>
        @endif

        <h1 class="text-2xl font-bold mb-4">Reservatie status</h1>

        <div class="border p-4 rounded mb-4">
            <div><b>Code:</b> {{ $reservation->public_code }}</div>
            <div><b>Datum:</b> {{ $reservation->date->format('d/m/Y') }}</div>
            <div><b>Tijdslot:</b> {{ $reservation->timeslot_id }}</div>
            <div><b>Status:</b> {{ $reservation->status }}</div>
        </div>

        <a href="/reservation" class="inline-block rounded-md border border-black px-4 py-2 hover:bg-gray-100">
            Nieuwe reservatie
        </a>
    </div>
@endsection
