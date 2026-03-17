@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-muted-foreground']) }}>
    {{ $value ?? $slot }}
</label>
