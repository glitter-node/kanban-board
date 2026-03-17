@props([
    'width' => '7xl',
    'padding' => 'default',
])

@php
    $widthClass = match ($width) {
        'md' => 'layout-container-md',
        'lg' => 'layout-container-lg',
        'xl' => 'layout-container-xl',
        'full' => 'layout-container-full',
        default => 'layout-container-7xl',
    };

    $paddingClass = match ($padding) {
        'none' => 'layout-container-pad-none',
        'sm' => 'layout-container-pad-sm',
        default => 'layout-container-pad-default',
    };
@endphp

<div {{ $attributes->class(['layout-container', $widthClass, $paddingClass]) }}>
    {{ $slot }}
</div>
