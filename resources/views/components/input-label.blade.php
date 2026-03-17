@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium ui-subtle']) }}>
    {{ $value ?? $slot }}
</label>
