@props(['value'])

<label bg-surface text-secondary border border-border {{ $attributes->merge(['class' => 'block text-sm font-medium text-secondary']) }}>
    {{ $value ?? $slot }}
</label bg-surface text-secondary border border-border>
