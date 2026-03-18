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
            <section
                :data-column-id="column.id"
                class="ui-panel flex h-full w-[20rem] shrink-0 flex-col p-0"
            >
                <div class="ui-panel-header flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            data-column-handle
                            x-show="canEdit"
                            class="btn-icon cursor-grab bg-primary text-white"
                            aria-label bg-surface text-secondary border border-border="Drag column"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6h.01M8 12h.01M8 18h.01M16 6h.01M16 12h.01M16 18h.01"/></svg>
                        </button>
                        <div>
                            <h2 class="text-sm font-semibold text-foreground text-secondary" x-text="column.title"></h2>
                            <p class="text-xs text-secondary" x-text="column.cards.length + ' cards'"></p>
                        </div>
                    </div>
                </div>

                <div class="flex-1 px-3 py-3">
                    <div :data-column-id="column.id" data-cards-sortable class="space-y-3">
                        <template x-for="card in [...column.cards].sort((a, b) => a.order_key.localeCompare(b.order_key))" :key="card.id">
                            <article
                                :data-card-id="card.id"
                                class="ui-card-interactive cursor-pointer"
                                @click="openCard(card.id)"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="line-clamp-2 text-sm font-medium text-surface-foreground text-secondary" x-text="card.title"></h3>
                                    <span
                                        class="ui-badge bg-surface text-secondary border border-border ui-badge bg-surface text-secondary border border-border-sm"
                                        :class="{
                                            'ui-badge bg-surface text-secondary border border-border-success': card.priority <= 1,
                                            'ui-badge bg-surface text-secondary border border-border-warning': card.priority === 2,
                                            'ui-badge bg-surface text-secondary border border-border-error': card.priority >= 3,
                                        }"
                                        x-text="'P' + card.priority"
                                    ></span>
                                </div>

                                <p class="mt-2 line-clamp-2 text-xs text-surface-foreground text-secondary" x-text="card.description || 'No description'"></p>

                                <div class="mt-3 flex items-center justify-between gap-2 text-xs text-surface-foreground text-secondary">
                                    <span x-text="card.due_at ? new Date(card.due_at).toLocaleDateString() : 'No due date'"></span>
                                    <span
                                        x-show="card.assigned_user_id"
                                        class="ui-badge bg-surface text-secondary border border-border ui-badge bg-surface text-secondary border border-border-sm ui-badge bg-surface text-secondary border border-border-accent max-w-[9rem] truncate"
                                        x-text="users.find(user => Number(user.id) === Number(card.assigned_user_id))?.name || 'Assigned'"
                                    ></span>
                                </div>
                            </article>
                        </template>
                    </div>
                </div>
            </section>
        </template>
    </div>
</div>
