@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-xl space-y-4 p-6">
        <h1 class="text-3xl font-bold">{{ __('zoo.success.title') }}</h1>

        <div class="rounded-lg border border-black p-4">
            <p><span class="font-semibold">{{ __('zoo.reservation_code') }}:</span> {{ $reservation->public_code }}</p>
            <p><span class="font-semibold">{{ __('zoo.date') }}:</span> {{ $reservation->date->format('d/m/Y') }}</p>
            <p>
                <span class="font-semibold">{{ __('zoo.time') }}:</span>
                {{ $reservation->timeSlot->label ?? '—' }}
            </p>
            <p><span class="font-semibold">{{ __('zoo.success.number_visitors') }}:</span> {{ $reservation->visitors->count() }}</p>
        </div>

        <div class="rounded-lg border border-black p-4">
            <h2 class="text-xl font-semibold">{{ __('zoo.success.number_visitors') }}</h2>
            <ul class="list-disc pl-5">
                @foreach($reservation->visitors as $v)
                    <li>
                        {{ $v->firstname }} {{ $v->lastname }}
                        @if($v->subscription_nr)
                            <span class="text-sm text-gray-600">({{ __('zoo.subscription_nr') }}: {{ $v->subscription_nr }})</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>

        <a href="/reservation" class="inline-block rounded-md border border-black px-4 py-2 hover:bg-gray-100">
            {{ __('zoo.success.new_reservation') }}
        </a>
    </div>
@endsection
