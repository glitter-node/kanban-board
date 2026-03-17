@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl',
])

<x-ui.modal :name="$name" :show="$show" :max-width="$maxWidth" {{ $attributes }}>
    {{ $slot }}
</x-ui.modal>
