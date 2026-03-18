@props([
    'width' => '7xl',
    'padding' => 'default',
    'containerClass' => '',
])

<section {{ $attributes->class(['relative py-24']) }}>
    <div class="absolute inset-0 ui-gradient opacity-50 pointer-events-none"></div>
    <x-layout.container :width="$width" :padding="$padding" class="relative z-10 {{ $containerClass }}">
        {{ $slot }}
    </x-layout.container>
</section>
