<x-ui.panel padding="none" class="rounded-2xl">
    <div class="ui-panel-header px-4 py-3">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-semibold text-foreground">{{ $column['title'] ?? 'Column' }}</h2>
                <p class="ui-meta">{{ count($column['cards'] ?? []) }} cards</p>
            </div>
            @if($canEdit)
                <span class="ui-meta">Drag enabled</span>
            @endif
        </div>
    </div>

    <div class="space-y-3 p-3">
        @foreach(($column['cards'] ?? []) as $card)
            <livewire:board.card-item :card="$card" :key="'standalone-card-'.$card['id']" />
        @endforeach
    </div>
</x-ui.panel>
