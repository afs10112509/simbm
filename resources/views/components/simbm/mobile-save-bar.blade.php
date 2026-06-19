@props([
    'action',
    'label',
    'disabled' => false,
])

<div {{ $attributes->merge(['class' => 'simbm-sticky-save simbm-mobile-actions']) }}>
    <x-filament::button
        wire:click="{{ $action }}"
        size="lg"
        :disabled="$disabled"
    >
        {{ $label }}
    </x-filament::button>
</div>
