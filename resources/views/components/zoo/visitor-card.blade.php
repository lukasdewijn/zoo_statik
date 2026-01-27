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
        <div class="mt-3 flex flex-col gap-2">
            <x-zoo.field
                :label="__('zoo.firstname') . '*'"
                :model="'visitors.' . $index . '.voornaam'"
                :error="'visitors.' . $index . '.voornaam'"
            />

            <x-zoo.field
                :label="__('zoo.lastname') . '*'"
                :model="'visitors.' . $index . '.achternaam'"
                :error="'visitors.' . $index . '.achternaam'"
            />

            <x-zoo.field
                :label="__('zoo.subscription_nr')"
                :model="'visitors.' . $index . '.abonr'"
                :error="'visitors.' . $index . '.abonr'"
            />
        </div>

    </div>
</div>
