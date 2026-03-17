@props([
    'size' => 'sm',
])

@php
    $sizeClass = $size === 'md' ? 'ui-avatar-md' : 'ui-avatar-sm';
@endphp

<span {{ $attributes->class(['ui-avatar', $sizeClass]) }}>
    {{ $slot }}
</span>
