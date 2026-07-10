@props([
    'type' => 'warning',
])

<div {{ $attributes->merge(['class' => 'simbm-alert']) }}>
    {{ $slot }}
</div>
