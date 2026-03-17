@props([
    'as' => 'input',
    'size' => 'md',
    'disabled' => false,
])

@php
    $sizeClass = match ($size) {
        'sm' => 'ui-input-sm',
        'lg' => 'ui-input-lg',
        default => 'ui-input-md',
    };
@endphp

@if ($as === 'textarea')
    <textarea @disabled($disabled) {{ $attributes->class(['ui-input', $sizeClass, 'focus-ring']) }}>{{ $slot }}</textarea>
@elseif ($as === 'select')
    <select @disabled($disabled) {{ $attributes->class(['ui-input', $sizeClass, 'focus-ring']) }}>
        {{ $slot }}
    </select>
@else
    <input @disabled($disabled) {{ $attributes->merge(['type' => 'text'])->class(['ui-input', $sizeClass, 'focus-ring']) }}>
@endif
