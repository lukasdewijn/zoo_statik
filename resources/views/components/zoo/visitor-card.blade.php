@php
    /** @var int $index */
    /** @var array $visitor */
    $canRemove = $canRemove ?? false;
@endphp

<div class="rounded-xl border border-gray-200 bg-gray-50 p-4 shadow-sm">
    <div class="flex items-center justify-between mb-3">
        <p class="font-semibold text-gray-800">{{ __('zoo.form.visitors.label') }} {{ $index + 1 }}</p>

        @if ($canRemove)
            <button
                type="button"
                wire:click="removeVisitor('{{ $visitor['key'] ?? $index }}')"
                class="text-sm font-medium text-red-500 hover:text-red-700 transition"
            >
                {{ __('zoo.delete') }}
            </button>
        @endif
    </div>

    <div class="space-y-3">
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