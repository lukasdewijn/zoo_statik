<div>
    @if (session('success'))
        <div class="text-sm text-green-600">
            {{ session('success') }}
        </div>
    @endif
    <h1 class="font-bold text-3xl">{{ __('zoo.form.title') }}</h1>
    <form wire:submit.prevent="save" class="flex flex-col gap-2 justify-start">
        <div class="flex flex-col">
            <label>{{ __('zoo.form.date.label') }}</label>
            <input type="date" wire:model="date" class="w-fit border-2 border-black rounded-sm">
            @error('date')
            <div class="text-sm text-red-600">{{ $message }}</div>@enderror
        </div>
        <div class="flex flex-col">
            <label>{{ __('zoo.form.timeslot.label') }}</label>
            <select wire:model="timeslot_id" class="w-fit border-2 border-black rounded-sm">
                <option value="">{{ __('zoo.form.timeslot.placeholder') }}</option>

                @foreach ($timeslots as $timeslot)
                    <option value="{{ $timeslot->id }}">
                        {{ $timeslot->label }}
                    </option>
                @endforeach
            </select>
            @if ($this->showRemaining)
                <div class="text-sm">
                    {!! __('zoo.form.places_counter.places_left', ['count' => "<strong>$remaining</strong>"]) !!}
                </div>
            @endif
            @if ($this->isSoldOut)
                <div class="text-sm text-red-600">
                    {{ __('zoo.form.places_counter.no_places_left') }}
                </div>
            @endif
            @error('timeslot_id')
            <div class="text-sm text-red-600">{{ $message }}</div>@enderror
        </div>

        <div class="flex flex-col w-fit">
            <label>{{ __('zoo.email') }}*</label>

            <input
                type="email"
                wire:model.blur="contact_email"
                class="w-full max-w-sm border-2 border-black rounded-sm px-2 py-1"
            >

            @error('contact_email')
            <div class="text-sm text-red-600">{{ $message }}</div>
            @enderror
        </div>

        <div class="flex flex-col gap-4">
            <div class="flex flex-col" items-center gap-3>
                <h2 class="font-semibold text-xl">{{ __('zoo.form.visitors.title') }}</h2>
                <button
                    type="button"
                    wire:click="addVisitor"
                    @disabled(! $this->canAddVisitor)
                    class="w-fit rounded-md border border-black px-3 py-1 font-medium hover:bg-gray-100"
                >
                    {{ __('zoo.form.visitors.addbutton') }}
                </button>
            </div>
        </div>

        <div class="flex flex-row gap-2">
            @foreach ($visitors as $index => $visitor)
                <div wire:key="visitor-{{ $visitor['key'] }}">
                    <x-zoo.visitor-card
                        :index="$index"
                        :visitor="$visitor"
                        :can-remove="count($visitors) > 1"
                    />
                </div>
            @endforeach
        </div>

        <button
            type="submit"
            @disabled(! $this->canSubmit)
            class="w-fit rounded-lg border-2 border-black bg-yellow-400 px-6 py-2
           font-semibold text-black shadow-sm
           transition hover:bg-yellow-500 hover:shadow-md
           focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2"
        >
            <span class="relative z-10 transition group-hover:text-white">
                {{ __('zoo.form.reservations_button') }}
            </span>
            <span
                class="absolute inset-0 bg-black transform scale-x-0 origin-left
               transition-transform group-hover:scale-x-100"
            ></span>
        </button>
        <div>{{ __('') }}</div>

    </form>
</div>
