@props([
    'title',
    'subtitle' => null,
    'metaLabel' => null,
    'metaValue' => null,
])

<div {{ $attributes->merge(['class' => 'simbm-section-header']) }}>
    <div>
        <div class="simbm-section-header__title">{{ $title }}</div>
        @if ($subtitle)
            <div class="simbm-section-header__subtitle">{{ $subtitle }}</div>
        @endif
    </div>

    @if ($metaValue !== null)
        <div class="text-right">
            @if ($metaLabel)
                <div class="simbm-section-header__meta-label">{{ $metaLabel }}</div>
            @endif
            <div class="simbm-section-header__meta-value">{{ $metaValue }}</div>
        </div>
    @endif
</div>
