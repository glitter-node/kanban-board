<x-ui.card class="p-3">
    <div class="flex items-start justify-between gap-2">
        <h3 class="text-sm font-medium text-surface-foreground text-secondary">{{ $card['title'] ?? 'Untitled card' }}</h3>
        <x-ui.badge bg-surface text-secondary border border-border tone="accent" size="sm">
            P{{ $card['priority'] ?? 0 }}
        </x-ui.badge bg-surface text-secondary border border-border>
    </div>
    <p class="mt-2 text-xs text-surface-foreground text-secondary">{{ $card['description'] ?? 'No description' }}</p>
</x-ui.card>
