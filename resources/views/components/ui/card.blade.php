@props([
    'interactive' => false,
    'as' => 'div',
])

@php
    $classes = $interactive ? 'ui-card ui-card-interactive p-6' : 'ui-card p-6';
@endphp

<{{ $as }} {{ $attributes->class([$classes]) }}>
    {{ $slot }}
</{{ $as }}>
