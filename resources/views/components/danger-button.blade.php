@props(['type' => 'submit'])

<x-ui.button {{ $attributes }} :type="$type" variant="danger">
    {{ $slot }}
</x-ui.button>
