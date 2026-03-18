<div class="overflow-x-auto pb-4">
    <div x-ref="columnsContainer" class="flex min-h-[65vh] gap-4">
        <template x-if="columns.length === 0">
            <div class="w-full">
                <x-ui.state.empty
                    title="No columns yet"
                    description="Create your first workflow column to start moving work across the board."
                />
            </div>
        </template>

        <template x-for="column in columns" :key="column.id">
            <x-ui.panel
                as="section"
                padding="none"
                :data-column-id="column.id"
                data-column-panel
                class="flex h-full w-[20rem] shrink-0 flex-col"
                x-bind:class="{
                    'kanban-column-overlimit': columnOverLimit(column),
                    'kanban-column-atlimit': !columnOverLimit(column) && columnAtLimit(column),
                }"
            >
                <div class="ui-panel-header flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-ui.button
                            type="button"
                            variant="icon"
                            size="sm"
                            data-column-handle
                            x-show="canEdit"
                            class="cursor-grab"
                            aria-label="Drag column"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6h.01M8 12h.01M8 18h.01M16 6h.01M16 12h.01M16 18h.01"/>
                            </svg>
                        </x-ui.button>

                        <div>
                            <h2 class="text-sm font-semibold" x-text="column.title"></h2>
                            <p class="ui-meta" x-text="column.current_wip + ' cards'"></p>
                        </div>
                    </div>

                    <div class="text-right">
                        <template x-if="column.wip_limit">
                            <div>
                                <p class="ui-meta" x-text="'WIP ' + column.current_wip + '/' + column.wip_limit"></p>
                                <p class="ui-meta" x-text="column.average_time_hours + 'h avg stay'"></p>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="px-3 pt-3" x-show="column.wip_limit">
                    <div class="kanban-wip-track">
                        <div class="kanban-wip-bar" :class="columnUsageBarClass(column)"></div>
                    </div>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="ui-meta">Flow usage</span>
                        <x-ui.badge tone="warning" x-show="columnAtLimit(column)">
                            <span x-text="columnOverLimit(column) ? 'Over limit' : 'At limit'"></span>
                        </x-ui.badge>
                    </div>
                </div>

                <div class="flex-1 px-3 py-3">
                    <div :data-column-id="column.id" data-cards-sortable class="space-y-3">
                        <template x-if="column.cards.length === 0">
                            <x-ui.surface class="kanban-empty-slot px-4 py-5 text-center">
                                <p class="text-sm font-medium">No cards yet</p>
                                <p class="mt-1 text-xs ui-meta">Drop work here or create the next task for this step.</p>
                            </x-ui.surface>
                        </template>

                        <template x-for="card in [...column.cards].sort((a, b) => a.order_key.localeCompare(b.order_key))" :key="card.id">
                            <x-ui.card
                                as="article"
                                :data-card-id="card.id"
                                class="kanban-card cursor-pointer p-4"
                                interactive="true"
                                @click="openCard(card.id)"
                                x-bind:class="{
                                    'kanban-card-blocked': card.blocked,
                                    'kanban-card-next': isNextActionable(card),
                                    'kanban-card-stuck': cardIsStuck(card),
                                }"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-start gap-2">
                                        <x-ui.button
                                            type="button"
                                            variant="icon"
                                            size="sm"
                                            data-card-handle
                                            class="kanban-card-handle cursor-grab touch-none"
                                            aria-label="Drag card"
                                            @click.stop
                                            >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6h.01M8 12h.01M8 18h.01M16 6h.01M16 12h.01M16 18h.01"/>
                                            </svg>
                                        </x-ui.button>

                                        <div class="min-w-0 flex-1">
                                            <template x-if="editingCardId !== Number(card.id)">
                                                <h3
                                                    class="line-clamp-2 text-sm font-medium"
                                                    x-text="card.title"
                                                    @dblclick.stop="startInlineEdit(card)"
                                                ></h3>
                                            </template>
                                            <template x-if="editingCardId === Number(card.id)">
                                                <x-ui.surface class="p-0" @click.stop>
                                                    <input
                                                        type="text"
                                                        x-model="inlineCardTitle"
                                                        class="ui-input w-full rounded-xl border-0 bg-transparent px-3 py-2 text-sm outline-none"
                                                        @keydown.enter.prevent="saveInlineCardTitle(card.id)"
                                                        @keydown.escape.prevent="cancelInlineEdit()"
                                                        @blur="saveInlineCardTitle(card.id)"
                                                        x-init="$nextTick(() => $el.focus())"
                                                    >
                                                </x-ui.surface>
                                            </template>
                                        </div>
                                    </div>
                                    <span
                                        class="ui-badge ui-badge-sm"
                                        :class="{
                                            'ui-badge-success': card.priority <= 1,
                                            'ui-badge-warning': card.priority === 2,
                                            'ui-badge-error': card.priority >= 3,
                                        }"
                                        x-text="'P' + card.priority"
                                    ></span>
                                </div>

                                <p class="mt-2 line-clamp-2 text-xs ui-meta" x-text="card.description || 'No description'"></p>

                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <x-ui.badge tone="warning" x-show="card.blocked">Blocked</x-ui.badge>
                                    <x-ui.badge tone="info" x-show="isNextActionable(card)">Next pull</x-ui.badge>
                                    <x-ui.badge tone="error" x-show="cardIsStuck(card)">Stuck</x-ui.badge>
                                </div>

                                <p class="mt-2 text-xs ui-meta" x-show="card.blocked && card.blocked_reason" x-text="card.blocked_reason"></p>

                                <div class="mt-3 flex items-center justify-between gap-2 text-xs ui-meta">
                                    <span x-text="card.due_at ? new Date(card.due_at).toLocaleDateString() : 'No due date'"></span>
                                    <span
                                        x-show="card.assigned_user_id"
                                        class="ui-badge ui-badge-sm ui-badge-accent max-w-[9rem] truncate"
                                        x-text="users.find(user => Number(user.id) === Number(card.assigned_user_id))?.name || 'Assigned'"
                                    ></span>
                                </div>

                                <div class="kanban-card-actions mt-3 flex items-center justify-between gap-2" @click.stop>
                                    <div class="flex items-center gap-2">
                                        <x-ui.button
                                            type="button"
                                            variant="secondary"
                                            size="sm"
                                            aria-label="Open card details"
                                            @click.stop="openCard(card.id)"
                                        >
                                            Open
                                        </x-ui.button>
                                        <x-ui.button
                                            type="button"
                                            variant="secondary"
                                            size="sm"
                                            aria-label="Rename card"
                                            x-show="canEdit"
                                            @click.stop="startInlineEdit(card)"
                                        >
                                            Edit
                                        </x-ui.button>
                                        <x-ui.button
                                            type="button"
                                            variant="secondary"
                                            size="sm"
                                            aria-label="Assign card to me"
                                            x-show="canEdit && Number(card.assigned_user_id || 0) !== Number(currentUserId)"
                                            @click.stop="quickAssignToMe(card.id)"
                                        >
                                            Assign me
                                        </x-ui.button>
                                    </div>

                                    <x-ui.button
                                        type="button"
                                        variant="icon"
                                        size="sm"
                                        aria-label="Archive card"
                                        x-show="canEdit"
                                        @click.stop="archiveCardWithUndo(card.id, column.id)"
                                    >
                                        <span aria-hidden="true">×</span>
                                    </x-ui.button>
                                </div>

                                <div x-show="canEdit" class="mt-3 flex items-center justify-between gap-2" @click.stop>
                                    <div class="flex items-center gap-2">
                                        <x-ui.button
                                            type="button"
                                            variant="icon"
                                            size="sm"
                                            aria-label="Move card left"
                                            @click.stop="moveCardHorizontally(card.id, -1)"
                                        >
                                            <span aria-hidden="true">←</span>
                                        </x-ui.button>
                                        <x-ui.button
                                            type="button"
                                            variant="icon"
                                            size="sm"
                                            aria-label="Move card up"
                                            @click.stop="moveCardVertically(card.id, column.id, -1)"
                                        >
                                            <span aria-hidden="true">↑</span>
                                        </x-ui.button>
                                        <x-ui.button
                                            type="button"
                                            variant="icon"
                                            size="sm"
                                            aria-label="Move card down"
                                            @click.stop="moveCardVertically(card.id, column.id, 1)"
                                        >
                                            <span aria-hidden="true">↓</span>
                                        </x-ui.button>
                                        <x-ui.button
                                            type="button"
                                            variant="icon"
                                            size="sm"
                                            aria-label="Move card right"
                                            @click.stop="moveCardHorizontally(card.id, 1)"
                                        >
                                            <span aria-hidden="true">→</span>
                                        </x-ui.button>
                                    </div>

                                    <span class="ui-meta">Move</span>
                                </div>
                            </x-ui.card>
                        </template>
                    </div>
                </div>
            </x-ui.panel>
        </template>
    </div>
</div>
