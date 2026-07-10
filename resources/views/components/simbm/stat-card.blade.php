@props([
    'label',
    'value',
    'hint' => null,
    'tone' => 'default',
])

@php
    $tones = [
        'default' => '',
        'success' => 'text-success-600 dark:text-success-400',
        'danger' => 'text-danger-600 dark:text-danger-400',
        'primary' => 'text-primary-600 dark:text-primary-400',
        'warning' => 'text-warning-600 dark:text-warning-400',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'simbm-stat']) }}>
    <div class="simbm-stat__label">{{ $label }}</div>
    <div class="simbm-stat__value {{ $tones[$tone] ?? '' }}">{{ $value }}</div>
    @if ($hint)
        <div class="simbm-stat__hint">{{ $hint }}</div>
    @endif
</div>
