@php
    /** @var int $index */
    /** @var array $visitor */
    $canRemove = $canRemove ?? false;
@endphp


<div class="rounded-md border w-fit border-black p-3">
    <div class="flex items-center justify-between">
        <p class="font-semibold">{{ __('zoo.form.visitors.label') }} {{ $index + 1 }}</p>

        @if ($canRemove)
            <button
                type="button"
                wire:click="removeVisitor('{{ $visitor['key'] }}')"
                class="text-sm underline hover:opacity-80"
            >
                {{ __('zoo.delete') }}
            </button>
        @endif
    </div>

    <div class="mt-3 flex flex-col gap-2">
        <div class="flex flex-col">
            <label>{{ __('zoo.firstname') }}*</label>
            <input
                type="text"
                wire:model="visitors.{{ $index }}.voornaam"
                class="w-fit border-2 pl-1 border-black rounded-sm"
            >
            @error("visitors.$index.voornaam")
            <div class="text-sm text-red-600">{{ $message }}</div>
            @enderror
        </div>

        <div class="flex flex-col">
            <label>{{ __('zoo.lastname') }}*</label>
            <input
                type="text"
                wire:model="visitors.{{ $index }}.achternaam"
                class="w-fit border-2 pl-1 border-black rounded-sm"
            >
            @error("visitors.$index.achternaam")
            <div class="text-sm text-red-600">{{ $message }}</div>
            @enderror
        </div>

        <div class="flex flex-col">
            <label>{{ __('zoo.subscription_nr') }}</label>
            <input
                type="text"
                wire:model="visitors.{{ $index }}.abonr"
                class="w-fit border-2 pl-1 border-black rounded-sm"
            >
            @error("visitors.$index.abonr")
            <div class="text-sm text-red-600">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
