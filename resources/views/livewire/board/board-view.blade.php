<div
    x-data="kanbanBoardView({
        board: @js($boardPayload),
        boardId: @js($board->getKey()),
        currentUserId: @js($currentUserId),
        currentUserName: @js($currentUserName),
        currentRole: @js($currentRole),
        canEdit: @js($canEdit),
        columns: @js($columns),
        members: @js($members),
        users: @js($users),
        activities: @js($activities),
    })"
    x-init="init()"
    class="min-h-[calc(100vh-8rem)]"
>
    <template x-if="booting">
        <x-layout.container class="py-6">
            <x-layout.grid :lg="null" class="lg:grid-cols-[minmax(0,1fr)_320px]">
                <x-layout.stack space="4">
                    <x-ui.state.skeleton :lines="4" />
                    <x-layout.grid :lg="null" class="md:grid-cols-2 lg:grid-cols-3">
                        <x-ui.state.skeleton :lines="3" />
                        <x-ui.state.skeleton :lines="3" />
                        <x-ui.state.skeleton :lines="3" />
                    </x-layout.grid>
                </x-layout.stack>
                <x-layout.stack space="4">
                    <x-ui.state.skeleton :lines="3" :avatar="true" />
                    <x-ui.state.skeleton :lines="4" :avatar="true" />
                </x-layout.stack>
            </x-layout.grid>
        </x-layout.container>
    </template>

    <template x-if="boardError">
        <x-layout.container class="py-6">
            <x-ui.state.error title="Board unavailable" retryLabel="Reload" x-bind:message="boardError" @click="window.location.reload()">
            </x-ui.state.error>
        </x-layout.container>
    </template>

    <div x-show="!booting && !boardError">
        <x-ui.surface class="rounded-none border-x-0 border-t-0 px-0 py-0">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <div class="min-w-0 flex-1">
                    <h1 class="truncate text-2xl font-semibold" x-text="board.title"></h1>
                    <p class="mt-1 truncate text-sm ui-meta" x-show="board.description" x-text="board.description"></p>
                    <div class="mt-3 flex flex-wrap items-center gap-3 text-xs ui-meta">
                        <x-ui.badge>
                            <span x-text="(boardAverageCycleTime() ?? 'N/A') + 'h avg cycle'"></span>
                        </x-ui.badge>
                        <x-ui.badge tone="success">
                            <span x-text="completedThisWeek() + ' completed this week'"></span>
                        </x-ui.badge>
                        <x-ui.badge tone="warning" x-show="blockedCardsCount() > 0">
                            <span x-text="blockedCardsCount() + ' blocked'"></span>
                        </x-ui.badge>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="hidden items-center gap-2 sm:flex">
                        <template x-for="user in onlineUsers" :key="user.id">
                            <x-ui.avatar
                                :title="user.name"
                                x-text="initials(user.name)"
                            ></x-ui.avatar>
                        </template>
                    </div>

                    <livewire:board.notification-bell
                        :user-id="$currentUserId"
                        :initial-notifications="$notifications"
                        :key="'notification-bell-'.$board->getKey().'-'.$currentUserId"
                    />
                </div>
            </div>
        </x-ui.surface>

        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-6 px-4 py-6 sm:px-6 lg:grid-cols-[minmax(0,1fr)_320px] lg:px-8">
            <section class="min-w-0">
                <livewire:board.column-list
                    :board-id="$board->getKey()"
                    :can-edit="$canEdit"
                    :key="'column-list-'.$board->getKey()"
                />
            </section>

            <aside class="space-y-6">
                <livewire:board.member-list
                    :can-edit="$canEdit"
                    :key="'member-list-'.$board->getKey()"
                />

                <livewire:board.activity-feed
                    :key="'activity-feed-'.$board->getKey()"
                />
            </aside>
        </div>

        <livewire:board.card-modal
            :board-id="$board->getKey()"
            :users="$users"
            :can-edit="$canEdit"
            :key="'card-modal-'.$board->getKey()"
        />

        <div class="toast-container" aria-live="polite" aria-atomic="true">
            <template x-for="toast in toasts" :key="toast.id">
                <div class="toast-item" :class="{
                    'border-success text-success': toast.tone === 'success',
                    'border-error text-error': toast.tone === 'error',
                    'border-info text-info': toast.tone === 'info'
                }">
                    <div class="flex items-center gap-3">
                        <span class="flex-1" x-text="toast.message"></span>
                        <x-ui.button
                            type="button"
                            variant="secondary"
                            size="sm"
                            x-show="toast.actionLabel"
                            @click="handleToastAction(toast.id)"
                        >
                            <span x-text="toast.actionLabel"></span>
                        </x-ui.button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
