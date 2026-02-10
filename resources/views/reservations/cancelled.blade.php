@extends('layouts.app')

@section('content')
    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-green-50 to-amber-50 px-4 sm:px-6 lg:px-8 -m-6">
        <div class="mx-auto w-full max-w-5xl">

            {{-- Two-column layout --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Left: Cancelled info --}}
                <div class="rounded-2xl bg-white shadow-lg p-6 flex flex-col justify-between">

                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900 mb-4">Reservatie status</h1>

                        @if (session('success'))
                            <div class="mb-4 rounded-xl border border-green-300 bg-green-50 p-4 text-green-800 text-sm font-medium">
                                {{ session('success') }}
                            </div>
                        @endif

                        {{-- Details grid --}}
                        <div class="grid grid-cols-2 gap-3 mb-6">
                            <div class="rounded-xl bg-gray-50 p-3">
                                <p class="text-xs font-medium text-gray-500">Code</p>
                                <p class="mt-0.5 text-base font-bold text-gray-900 font-mono">{{ $reservation->public_code }}</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3">
                                <p class="text-xs font-medium text-gray-500">Datum</p>
                                <p class="mt-0.5 text-base font-bold text-gray-900">{{ $reservation->date->format('d/m/Y') }}</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3">
                                <p class="text-xs font-medium text-gray-500">Tijdslot</p>
                                <p class="mt-0.5 text-base font-bold text-gray-900">{{ $reservation->timeSlot->label }}</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3">
                                <p class="text-xs font-medium text-gray-500">Status</p>
                                <p class="mt-0.5 text-base font-bold text-red-600">{{ $reservation->status->value }}</p>
                            </div>
                        </div>
                    </div>

                    <a
                        href="/reservation"
                        class="block text-center rounded-xl bg-amber-500 px-6 py-3 font-bold text-white shadow-md transition hover:bg-amber-600 hover:shadow-lg focus:ring-2 focus:ring-amber-300 focus:ring-offset-2"
                    >
                        Nieuwe reservatie
                    </a>
                </div>

                {{-- Right: Angry capybara --}}
                <div class="overflow-hidden rounded-2xl bg-white shadow-lg flex flex-col">
                    <div class="relative flex-1">
                        <img
                            src="/images/angry_capy.jpg"
                            alt="Angry capybara"
                            class="w-full h-full object-cover min-h-64"
                        >
                        <div class="absolute inset-0 bg-gradient-to-b from-black/30 to-transparent"></div>
                        <p class="absolute top-6 left-6 right-6 text-2xl font-extrabold text-white drop-shadow-lg leading-tight">
                            You really did it... See you next time!
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection