@props([
    'interactive' => false,
])

<article {{ $attributes->class([$interactive ? 'ui-card-interactive' : 'ui-card']) }}>
    {{ $slot }}
</article>
