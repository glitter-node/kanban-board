@props(['active'])

@php
$classes = ($active ?? false)
            ? 'motion inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 border-ui-brand-primary text-foreground focus-ring'
            : 'motion inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 ui-muted hover:text-foreground hover:border-border focus-ring focus:text-foreground focus:border-border';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
