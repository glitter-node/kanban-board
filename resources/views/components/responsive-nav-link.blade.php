@props(['active'])

@php
$classes = ($active ?? false)
            ? 'motion block w-full border-l-4 border-ui-brand-primary bg-elevated ps-3 pe-4 py-2 text-start text-base font-medium text-foreground focus-ring'
            : 'motion block w-full border-l-4 border-background ps-3 pe-4 py-2 text-start text-base font-medium text-muted-foreground hover:bg-elevated hover:text-foreground hover:border-border focus-ring focus:bg-elevated focus:text-foreground focus:border-border';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
