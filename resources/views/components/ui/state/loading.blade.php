@props([
    'label' => 'Loading',
])

<x-layout.stack class="items-center justify-center py-10 text-center">
    <div class="spinner text-primary-foreground" aria-hidden="true"></div>
    <p class="text-sm ui-muted">{{ $label }}</p>
</x-layout.stack>
