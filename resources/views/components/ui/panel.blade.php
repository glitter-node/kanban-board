@props([
    'as' => 'section',
    'padding' => 'default',
    'header' => false,
    'elevated' => false,
])

@php
    $panelClass = $elevated ? 'ui-panel-elevated' : 'ui-panel';

    $paddingClass = match ($padding) {
        'none' => '',
        'sm' => 'p-4',
        'lg' => 'p-6',
        default => 'p-5',
    };
@endphp

<{{ $as }} {{ $attributes->class([$panelClass, $paddingClass]) }}>
    {{ $slot }}
</{{ $as }}>
