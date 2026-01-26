<x-filament-panels::page>
    <div class="grid gap-6">
        {{-- Filters card --}}
        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:gap-6">
                <div class="w-full sm:max-w-xs">
                    <div class="mb-2 text-sm font-medium text-white/80">From</div>

                    <x-filament::input.wrapper class="w-full">
                        <x-filament::input
                            type="date"
                            wire:model.live="from"
                            max="{{ $this->to }}"
                            class="w-full"
                        />
                    </x-filament::input.wrapper>
                </div>

                <div class="w-full sm:max-w-xs">
                    <div class="mb-2 text-sm font-medium text-white/80">To</div>

                    <x-filament::input.wrapper class="w-full">
                        <x-filament::input
                            type="date"
                            wire:model.live="to"
                            min="{{ $this->from }}"
                            class="w-full"
                        />
                    </x-filament::input.wrapper>
                </div>
                <br>
            </div>
        </div>

        @livewire(
            \App\Filament\Widgets\ReservationsTimeSeries::class,
            ['from' => $this->from, 'to' => $this->to],
            key('visitors-chart-' . $this->from . '-' . $this->to)
        )


    </div>
</x-filament-panels::page>
