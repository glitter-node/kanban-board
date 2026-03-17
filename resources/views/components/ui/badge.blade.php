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

    $sizeClass = $size === 'sm' ? 'ui-badge-sm' : 'ui-badge';
@endphp

<span {{ $attributes->class([$sizeClass, $toneClass]) }}>
    {{ $slot }}
</span>
