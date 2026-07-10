@props([
    'label',
    'for' => null,
])

<div {{ $attributes->merge(['class' => 'simbm-field']) }}>
    <label @if ($for) for="{{ $for }}" @endif class="simbm-field__label">
        {{ $label }}
    </label>
    {{ $slot }}
</div>
