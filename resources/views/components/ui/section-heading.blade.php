@props([
    'kicker' => null,
    'count' => null,
])

<div class="flex items-center justify-between">
    <div>
        @if ($kicker)
            <p class="ui-kicker text-secondary">{{ $kicker }}</p>
        @endif
        <h2 class="mt-1 ui-title">{{ $slot }}</h2>
    </div>

    @if ($count !== null)
        <x-ui.badge>{{ $count }}</x-ui.badge>
    @endif
</div>
