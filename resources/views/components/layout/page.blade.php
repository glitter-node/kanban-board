@props([
    'as' => 'div',
])

<{{ $as }} {{ $attributes->class(['layout-page']) }}>
    {{ $slot }}
</{{ $as }}>
