@props(['type' => 'submit'])

<x-ui.button {{ $attributes }} :type="$type" variant="primary">
    {{ $slot }}
</x-ui.button>
