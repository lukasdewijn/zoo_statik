@php
    $type = $type ?? 'text';
@endphp

<div class="space-y-1">
    <label class="block text-sm font-medium text-gray-600">{{ $label }}</label>

    <input
        type="{{ $type }}"
        wire:model="{{ $model }}"
        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200 transition"
    >

    @error($error)
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>