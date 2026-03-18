@props(['value'])

<label {{ $attributes->except(['bg-surface', 'text-secondary', 'border', 'border-border'])->merge(['class' => 'block text-sm font-medium text-secondary']) }}>
    {{ $value ?? $slot }}
</label>
