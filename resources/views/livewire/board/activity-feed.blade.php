<x-ui.panel padding="none">
    <div class="ui-panel-header">
        <div class="flex items-center justify-between">
            <div>
                <p class="ui-kicker text-secondary">Activity</p>
                <h2 class="mt-1 ui-title">Live feed</h2>
            </div>
            <x-ui.badge bg-surface text-secondary border border-border x-text="activities.length"></x-ui.badge bg-surface text-secondary border border-border>
        </div>
    </div>

    <div class="ui-panel-body max-h-[28rem] space-y-3 overflow-y-auto">
        <template x-if="booting">
            <x-layout.stack space="3">
                <x-ui.state.skeleton :lines="3" :avatar="true" />
                <x-ui.state.skeleton :lines="3" :avatar="true" />
            </x-layout.stack>
        </template>

        <template x-if="!booting && activities.length === 0">
            <x-ui.state.empty
                title="No activity yet"
                description="Board actions will appear here as your team creates, moves, and updates cards."
            />
        </template>

        <template x-if="!booting">
        <template x-for="activity in activities" :key="activity.id">
            <x-ui.card class="p-4">
                <div class="flex items-start gap-3">
                    <x-ui.avatar class="mt-1" x-text="initials(activity.actor_name || 'System')"></x-ui.avatar>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-foreground text-secondary" x-text="activity.actor_name || 'System'"></p>
                        <p class="mt-1 text-sm text-secondary" x-text="activity.action"></p>
                        <p class="mt-1 text-xs text-secondary" x-text="activity.created_at ? new Date(activity.created_at).toLocaleString() : ''"></p>
                    </div>
                </div>
            </x-ui.card>
        </template>
        </template>
    </div>
</x-ui.panel>
