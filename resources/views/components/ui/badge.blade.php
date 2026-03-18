@props([
    'tone' => 'default',
    'size' => 'default',
])

@php
    $toneClass = match ($tone) {
        'accent' => 'ui-badge-accent',
        'success' => 'ui-badge-success',
        'warning' => 'ui-badge-warning',
        'error' => 'ui-badge-error',
        'info' => 'ui-badge-info',
        default => '',
    };

    $sizeClass = $size === 'sm' ? 'ui-badge-sm' : '';
@endphp

<span {{ $attributes->except(['bg-surface', 'text-secondary', 'border', 'border-border'])->class(['ui-badge', $sizeClass, $toneClass]) }}>
    {{ $slot }}
</span>
