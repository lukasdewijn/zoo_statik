@extends('layouts.app')

@section('content')
    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-green-50 to-amber-50 px-4 sm:px-6 lg:px-8 -m-6">
        <div class="mx-auto w-full max-w-5xl">

            {{-- Header --}}
            <div class="text-center mb-6">
                <h1 class="text-4xl font-extrabold text-green-900 tracking-tight">
                    {{ __('zoo.success.title') }}
                </h1>
                <p class="mt-2 text-green-700 font-medium">
                    {{ __('zoo.success.confirmation_sent', ['email' => $reservation->contact_email]) }}
                </p>
            </div>

            {{-- Two-column layout --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Left: Reservation info --}}
                <div class="rounded-2xl bg-white shadow-lg p-6 flex flex-col justify-between">

                    {{-- Details grid --}}
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs font-medium text-gray-500">{{ __('zoo.reservation_code') }}</p>
                            <p class="mt-0.5 text-base font-bold text-gray-900 font-mono">{{ $reservation->public_code }}</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs font-medium text-gray-500">{{ __('zoo.date') }}</p>
                            <p class="mt-0.5 text-base font-bold text-gray-900">{{ $reservation->date->format('d/m/Y') }}</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs font-medium text-gray-500">{{ __('zoo.time') }}</p>
                            <p class="mt-0.5 text-base font-bold text-gray-900">{{ $reservation->timeSlot->label ?? '—' }}</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs font-medium text-gray-500">{{ __('zoo.success.number_visitors') }}</p>
                            <p class="mt-0.5 text-base font-bold text-gray-900">{{ $reservation->visitors->count() }}</p>
                        </div>
                    </div>

                    {{-- Visitors list --}}
                    <div class="mb-4">
                        <h2 class="text-lg font-bold text-gray-800 mb-2">{{ __('zoo.success.number_visitors') }}</h2>
                        <div class="space-y-1.5">
                            @foreach($reservation->visitors as $v)
                                <div class="flex items-center gap-3 rounded-lg bg-gray-50 px-3 py-2">
                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-green-600 text-xs font-bold text-white">
                                        {{ $loop->iteration }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-900 text-sm truncate">{{ $v->first_name }} {{ $v->last_name }}</p>
                                        @if($v->subscription_number)
                                            <p class="text-xs text-gray-500">{{ __('zoo.subscription_nr') }}: {{ $v->subscription_number }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- New reservation button --}}
                    <a
                        href="/reservation"
                        class="block text-center rounded-xl bg-amber-500 px-6 py-2.5 font-bold text-white shadow-md transition hover:bg-amber-600 hover:shadow-lg focus:ring-2 focus:ring-amber-300 focus:ring-offset-2"
                    >
                        {{ __('zoo.success.new_reservation') }}
                    </a>
                </div>

                {{-- Right: Capybara of the day --}}
                @if (!empty($capybara['url']))
                    <div class="overflow-hidden rounded-2xl bg-white shadow-lg flex flex-col">
                        <img
                            src="{{ $capybara['url'] }}"
                            alt="{{ $capybara['alt'] ?? 'Capybara of the day' }}"
                            class="w-full flex-1 object-cover"
                        >
                        <div class="p-5">
                            <p class="text-sm font-semibold uppercase tracking-wide text-amber-600 mb-1">Capybara of the day</p>
                            @if (!empty($capybara['fact']))
                                <p class="text-gray-700 italic leading-relaxed">"{{ $capybara['fact'] }}"</p>
                            @endif
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection