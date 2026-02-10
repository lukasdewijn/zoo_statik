<div class="min-h-screen bg-gradient-to-br from-green-50 to-amber-50 py-8 px-4 sm:px-6 lg:px-8 -m-6">
    <div class="mx-auto max-w-3xl">

        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-4xl font-extrabold text-green-900 tracking-tight">
                {{ __('zoo.form.title') }}
            </h1>
        </div>

        {{-- Capybara --}}
        @if ($capybaraUrl)
            <div class="mb-8 overflow-hidden rounded-2xl bg-white shadow-lg">
                <div class="flex flex-col sm:flex-row">
                    <div class="sm:w-1/2">
                        <img
                            src="{{ $capybaraUrl }}"
                            alt="{{ $capybaraAlt }}"
                            class="h-64 w-full object-cover sm:h-full"
                        >
                    </div>
                    @if ($capybaraFact)
                        <div class="flex items-center p-6 sm:w-1/2">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-wide text-amber-600 mb-2">Capybara fact</p>
                                <p class="text-gray-700 text-lg italic leading-relaxed">"{{ $capybaraFact }}"</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Success message --}}
        @if (session('success'))
            <div class="mb-6 rounded-xl border border-green-300 bg-green-50 p-4 text-green-800 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        {{-- Reservation form --}}
        <div class="rounded-2xl bg-white shadow-lg p-6 sm:p-8">
            <form wire:submit.prevent="save" class="space-y-6">

                {{-- Date & time row --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    {{-- Date --}}
                    <div class="space-y-1">
                        <label class="block text-sm font-semibold text-gray-700">{{ __('zoo.form.date.label') }}</label>
                        <div wire:ignore
                             x-data="{ dates: @js($availableDates) }"
                             x-init="flatpickr($refs.dateInput, {
                                 dateFormat: 'Y-m-d',
                                 altInput: true,
                                 altFormat: 'j F Y',
                                 enable: dates,
                                 minDate: 'today',
                                 onChange(selectedDates, dateStr) { $wire.set('date', dateStr) }
                             })">
                            <input
                                x-ref="dateInput"
                                type="text"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200 transition"
                                placeholder="{{ __('zoo.form.date.label') }}"
                            >
                        </div>
                        @error('date')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Timeslot --}}
                    <div class="space-y-1">
                        <label class="block text-sm font-semibold text-gray-700">{{ __('zoo.form.timeslot.label') }}</label>
                        <select
                            wire:model.live="time_slot_id"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200 transition disabled:bg-gray-100 disabled:text-gray-400"
                            @disabled(!$date)
                        >
                            <option value="">{{ __('zoo.form.timeslot.placeholder') }}</option>
                            @foreach ($timeslots as $timeslot)
                                @if (isset($timeslotAvailability[$timeslot->id]))
                                    <option value="{{ $timeslot->id }}" @disabled($timeslotAvailability[$timeslot->id] <= 0)>
                                        {{ $timeslot->label }}@if($timeslotAvailability[$timeslot->id] <= 0) (volzet)@endif
                                    </option>
                                @endif
                            @endforeach
                        </select>

                        @if ($this->showRemaining)
                            <p class="text-sm text-green-700">
                                {!! __('zoo.form.places_counter.places_left', ['count' => "<strong>$remaining</strong>"]) !!}
                            </p>
                        @endif
                        @if ($this->isSoldOut)
                            <p class="text-sm font-medium text-red-600">
                                {{ __('zoo.form.places_counter.no_places_left') }}
                            </p>
                        @endif
                        @error('time_slot_id')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Email --}}
                <div class="space-y-1">
                    <label class="block text-sm font-semibold text-gray-700">{{ __('zoo.email') }}*</label>
                    <input
                        type="email"
                        wire:model.blur="contact_email"
                        class="w-full max-w-md rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200 transition"
                    >
                    @error('contact_email')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Visitors --}}
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-800">{{ __('zoo.form.visitors.title') }}</h2>
                        <button
                            type="button"
                            wire:click="addVisitor"
                            @disabled(!$this->canAddVisitor)
                            class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 focus:ring-2 focus:ring-green-300 disabled:bg-gray-300 disabled:cursor-not-allowed"
                        >
                            {{ __('zoo.form.visitors.addbutton') }}
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach ($visitors as $index => $visitor)
                            <div wire:key="visitor-{{ $visitor['key'] ?? $index }}">
                                <x-zoo.visitor-card
                                    :index="$index"
                                    :visitor="$visitor"
                                    :can-remove="count($visitors) > 1"
                                />
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Submit --}}
                <div class="pt-2">
                    <button
                        type="submit"
                        @disabled(!$this->canSubmit)
                        class="w-full sm:w-auto rounded-xl bg-amber-500 px-8 py-3 text-lg font-bold text-white shadow-md transition hover:bg-amber-600 hover:shadow-lg focus:ring-2 focus:ring-amber-300 focus:ring-offset-2 disabled:bg-gray-300 disabled:cursor-not-allowed"
                    >
                        {{ __('zoo.form.reservations_button') }}
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
