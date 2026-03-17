<x-ui.panel padding="none">
    <div class="ui-panel-header">
        <div class="flex items-center justify-between">
            <div>
                <p class="ui-kicker">Members</p>
                <h2 class="mt-1 ui-title">Team</h2>
            </div>
            <x-ui.badge x-text="members.length"></x-ui.badge>
        </div>
    </div>

    <div class="ui-panel-body space-y-3">
        <template x-if="booting">
            <x-layout.stack space="3">
                <x-ui.state.skeleton :lines="2" :avatar="true" />
                <x-ui.state.skeleton :lines="2" :avatar="true" />
            </x-layout.stack>
        </template>

        <template x-if="!booting && members.length === 0">
            <x-ui.state.empty
                title="No members yet"
                description="Invite teammates to collaborate on this board."
            />
        </template>

        <template x-if="!booting">
        <template x-for="member in members" :key="member.id">
            <x-ui.card class="p-4">
                <div class="flex items-center gap-3">
                    <x-ui.avatar size="md" x-text="initials(member.name)"></x-ui.avatar>
                    <div>
                        <p class="text-sm font-medium text-foreground" x-text="member.name"></p>
                        <p class="text-xs text-muted-foreground" x-text="member.email"></p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <x-ui.badge x-text="member.role"></x-ui.badge>
                    <span x-show="onlineUsers.find(user => Number(user.id) === Number(member.user_id))" class="connection-dot connected" title="Online"></span>
                </div>
            </x-ui.card>
        </template>
        </template>
    </div>
</x-ui.panel>
