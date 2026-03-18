@props([
    'label bg-surface text-secondary border border-border' => 'Loading',
])

<x-layout.stack class="items-center justify-center py-10 text-center">
    <div class="spinner text-primary-foreground" aria-hidden="true"></div>
    <p class="text-sm text-secondary">{{ $label bg-surface text-secondary border border-border }}</p>
</x-layout.stack>
