@props([
    'as' => 'section',
    'spacing' => 'default',
])

@php
    $spacingClass = match ($spacing) {
        'none' => 'layout-section-none',
        'sm' => 'layout-section-sm',
        'lg' => 'layout-section-lg',
        default => 'layout-section',
    };
@endphp

<{{ $as }} {{ $attributes->class([$spacingClass, 'ui-gradient']) }}>
    {{ $slot }}
</{{ $as }}>
