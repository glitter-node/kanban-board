@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => false,
    'type' => 'button',
    'as' => 'button',
])

@php
    $isIcon = $icon || $variant === 'icon';

    $sizeClass = match ($size) {
        'sm' => $isIcon ? 'h-9 w-9 p-0' : 'px-4 py-2 text-sm',
        'lg' => $isIcon ? 'h-11 w-11 p-0' : 'px-6 py-3 text-sm',
        default => $isIcon ? 'h-10 w-10 p-0' : 'px-5 py-2 text-sm',
    };

    $base = 'focus-ring inline-flex items-center justify-center gap-2 rounded-lg transition-colors';

    $variantClass = match ($variant) {
        'secondary' => 'border border-[var(--border)] text-[var(--text-primary)]',
        'danger' => 'bg-[var(--danger)] text-[var(--danger-foreground)]',
        'ghost' => 'border border-[var(--border)] text-[var(--text-primary)]',
        'icon' => 'ui-surface-elevated border border-[var(--border)] text-[var(--text-primary)]',
        default => 'bg-[var(--primary)] text-[var(--primary-foreground)] ui-glow',
    };
@endphp

@if ($as === 'a')
    <a {{ $attributes->class([$base, $variantClass, $sizeClass]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => $type])->class([$base, $variantClass, $sizeClass]) }}>
        {{ $slot }}
    </button>
@endif
