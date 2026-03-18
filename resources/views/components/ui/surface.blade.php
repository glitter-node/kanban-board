@props([
    'variant' => 'default',
    'as' => 'div',
])

@php
    $base = $variant === 'elevated'
        ? 'ui-surface-elevated p-6 rounded-xl'
        : 'ui-surface p-6 rounded-xl';
@endphp

<{{ $as }} {{ $attributes->class([$base]) }}>
    {{ $slot }}
</{{ $as }}>
