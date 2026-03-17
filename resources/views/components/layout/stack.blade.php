@props([
    'space' => '6',
    'as' => 'div',
])

@php
    $spaceClass = match ($space) {
        '0' => 'layout-stack-0',
        '2' => 'layout-stack-2',
        '3' => 'layout-stack-3',
        '4' => 'layout-stack-4',
        '8' => 'layout-stack-8',
        '10' => 'layout-stack-10',
        '12' => 'layout-stack-12',
        default => 'layout-stack-6',
    };
@endphp

<{{ $as }} {{ $attributes->class(['layout-stack', $spaceClass]) }}>
    {{ $slot }}
</{{ $as }}>
