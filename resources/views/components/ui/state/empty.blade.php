@props([
    'title' => null,
    'description' => null,
    'actionLabel' => null,
    'actionHref' => null,
])

@php
    $resolvedTitle = $title ?? trim((string) $slot);
@endphp

<x-ui.card class="p-6">
    <x-layout.stack class="items-center text-center" space="4">
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-surface text-muted-foreground ring-1 ring-border">
            <span class="text-lg">+</span>
        </div>
        <x-layout.stack space="2">
            <h3 class="text-base font-semibold text-foreground">{{ $resolvedTitle }}</h3>
            @if ($description)
                <p class="max-w-md text-sm text-muted-foreground">{{ $description }}</p>
            @endif
        </x-layout.stack>
        @if ($actionLabel && $actionHref)
            <x-ui.button variant="primary" size="sm" :type="'button'" onclick="window.location.href='{{ $actionHref }}'">
                {{ $actionLabel }}
            </x-ui.button>
        @elseif (isset($action))
            {{ $action }}
        @endif
    </x-layout.stack>
</x-ui.card>
