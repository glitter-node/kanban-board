@props([
    'tone' => 'default',
    'size' => 'default',
])

@php
    $toneClass = match ($tone) {
        'accent' => 'ui-badge bg-surface text-secondary border border-border-accent',
        'success' => 'ui-badge bg-surface text-secondary border border-border-success',
        'warning' => 'ui-badge bg-surface text-secondary border border-border-warning',
        'error' => 'ui-badge bg-surface text-secondary border border-border-error',
        'info' => 'ui-badge bg-surface text-secondary border border-border-info',
        default => '',
    };

    $sizeClass = $size === 'sm' ? 'ui-badge bg-surface text-secondary border border-border-sm' : 'ui-badge bg-surface text-secondary border border-border';
@endphp

<span {{ $attributes->class([$sizeClass, $toneClass]) }}>
    {{ $slot }}
</span>
