@props([
    'text' => null,
    'position' => 'top',
])

@php
    $content = $text ?? trim((string) $slot);

    $positionClass = match ($position) {
        'bottom' => 'top-full left-1/2 mt-2 -translate-x-1/2',
        'left' => 'right-full top-1/2 mr-2 -translate-y-1/2',
        'right' => 'left-full top-1/2 ml-2 -translate-y-1/2',
        default => 'bottom-full left-1/2 mb-2 -translate-x-1/2',
    };
@endphp

<span x-data="{ open: false }" class="relative inline-flex">
    <span
        @mouseenter="open = true"
        @mouseleave="open = false"
        @focusin="open = true"
        @focusout="open = false"
        class="inline-flex"
    >
        {{ $trigger }}
    </span>

    <span
        x-cloak
        x-show="open"
        x-transition:enter="transition-all duration-fast ease-standard"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition-all duration-fast ease-standard"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="ui-tooltip-content motion {{ $positionClass }}"
        role="tooltip"
    >
        {{ $content }}
    </span>
</span>
