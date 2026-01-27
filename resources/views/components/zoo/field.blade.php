@php
    $type = $type ?? 'text';
@endphp

<div class="flex flex-col">
    <label>{{ $label }}</label>

    <input
        type="{{ $type }}"
        wire:model="{{ $model }}"
        class="w-fit border-2 pl-1 border-black rounded-sm"
    >

    @error($error)
    <div class="text-sm text-red-600">{{ $message }}</div>
    @enderror
</div>
