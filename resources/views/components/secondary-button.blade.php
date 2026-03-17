@props(['type' => 'button'])

<x-ui.button {{ $attributes->class(['disabled:opacity-25']) }} :type="$type" variant="secondary">
    {{ $slot }}
</x-ui.button>
