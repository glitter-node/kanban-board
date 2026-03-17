@props(['active'])

@php
$classes = ($active ?? false)
            ? 'motion block w-full border-l-4 border-ui-brand-primary bg-elevated ps-3 pe-4 py-2 text-start text-base font-medium text-ui-text-primary focus-ring'
            : 'motion block w-full border-l-4 border-transparent ps-3 pe-4 py-2 text-start text-base font-medium ui-subtle hover:bg-elevated hover:text-ui-text-primary hover:border-border focus-ring focus:bg-elevated focus:text-ui-text-primary focus:border-border';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
