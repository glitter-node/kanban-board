@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => false,
    'type' => 'button',
])

@php
    $isIcon = $icon || $variant === 'icon';

    $variantClass = match ($variant) {
        'secondary' => 'btn-secondary',
        'danger' => 'btn-danger',
        'ghost' => 'btn-ghost',
        'icon' => 'btn-icon',
        default => 'btn-primary',
    };

    $sizeClass = match ($size) {
        'sm' => $isIcon ? 'btn-icon-sm' : 'btn-sm',
        'lg' => $isIcon ? 'btn-icon-md' : 'btn-lg',
        default => $isIcon ? 'btn-icon-md' : 'btn-md',
    };
@endphp

<button {{ $attributes->merge(['type' => $type])->class([$variantClass, $sizeClass, 'focus-ring']) }}>
    {{ $slot }}
</button>
