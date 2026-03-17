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
    <div class="ui-surface border-b border-border">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <div class="min-w-0">
                <h1 class="truncate text-2xl font-semibold text-foreground" x-text="board.title"></h1>
                <p class="mt-1 truncate text-sm text-muted-foreground" x-text="board.description || 'No description'"></p>
            </div>
            <div class="flex items-center gap-3">
                <div class="hidden items-center gap-2 sm:flex">
                    <template x-for="user in onlineUsers" :key="user.id">
                        <x-ui.avatar
                            class="shadow"
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
    </div>

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
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
        <script>
            function kanbanBoardView(config) {
                return {
                    board: config.board,
                    boardId: config.boardId,
                    currentUserId: config.currentUserId,
                    currentUserName: config.currentUserName,
                    currentRole: config.currentRole,
                    canEdit: config.canEdit,
                    columns: [...config.columns].sort((a, b) => a.order_key.localeCompare(b.order_key)),
                    members: config.members,
                    users: config.users,
                    activities: config.activities,
                    onlineUsers: [],
                    booting: true,
                    boardError: null,
                    selectedCard: null,
                    selectedColumnId: null,
                    selectedCardComments: [],
                    selectedCardCommentsLoading: false,
                    selectedCardCommentsError: null,
                    cardForm: {
                        id: null,
                        title: '',
                        description: '',
                        priority: 2,
                        assigned_user_id: null,
                        due_at: null,
                    },
                    loading: false,

                    init() {
                        try {
                            this.initColumnSortable();
                            this.initCardSortables();
                            this.initEcho();
                            window.addEventListener('board:open-card', (event) => this.openCard(event.detail.cardId));
                        } catch (error) {
                            console.error(error);
                            this.boardError = 'The board UI could not be initialized.';
                        } finally {
                            this.$nextTick(() => {
                                this.booting = false;
                            });
                        }
                    },

                    initials(name) {
                        return (name || '')
                            .split(' ')
                            .map(part => part.charAt(0))
                            .join('')
                            .slice(0, 2)
                            .toUpperCase();
                    },

                    apiBase(path = '') {
                        return `/api/v1/boards/${this.boardId}${path}`;
                    },

                    sortColumns() {
                        this.columns.sort((a, b) => a.order_key.localeCompare(b.order_key));
                    },

                    sortCards(column) {
                        column.cards.sort((a, b) => a.order_key.localeCompare(b.order_key));
                    },

                    findColumn(columnId) {
                        return this.columns.find(column => Number(column.id) === Number(columnId)) || null;
                    },

                    findCard(cardId) {
                        for (const column of this.columns) {
                            const card = column.cards.find(item => Number(item.id) === Number(cardId));
                            if (card) {
                                return { card, column };
                            }
                        }

                        return { card: null, column: null };
                    },

                    openCard(cardId) {
                        const { card, column } = this.findCard(cardId);
                        if (!card || !column) {
                            return;
                        }

                        this.selectedColumnId = column.id;
                        this.selectedCard = structuredClone(card);
                        this.selectedCardCommentsError = null;
                        this.cardForm = {
                            id: card.id,
                            title: card.title,
                            description: card.description,
                            priority: card.priority,
                            assigned_user_id: card.assigned_user_id,
                            due_at: card.due_at ? card.due_at.slice(0, 10) : null,
                        };

                        this.loadComments(card.id);
                        window.dispatchEvent(new CustomEvent('board:card-modal-open'));
                    },

                    closeCardModal() {
                        this.selectedCard = null;
                        this.selectedColumnId = null;
                        this.selectedCardComments = [];
                        this.selectedCardCommentsLoading = false;
                        this.selectedCardCommentsError = null;
                        this.cardForm = {
                            id: null,
                            title: '',
                            description: '',
                            priority: 2,
                            assigned_user_id: null,
                            due_at: null,
                        };
                        window.dispatchEvent(new CustomEvent('board:card-modal-close'));
                    },

                    async loadComments(cardId) {
                        this.selectedCardCommentsLoading = true;
                        this.selectedCardCommentsError = null;

                        try {
                            const response = await axios.get(this.apiBase(`/cards/${cardId}/comments`));
                            const payload = response.data.data;
                            this.selectedCardComments = Array.isArray(payload?.data) ? payload.data : (payload?.data ?? payload ?? []);
                        } catch (error) {
                            console.error(error);
                            this.selectedCardComments = [];
                            this.selectedCardCommentsError = 'Comments could not be loaded.';
                        } finally {
                            this.selectedCardCommentsLoading = false;
                        }
                    },

                    async saveCard() {
                        if (!this.selectedCard) {
                            return;
                        }

                        this.loading = true;

                        try {
                            const response = await axios.patch(this.apiBase(`/cards/${this.selectedCard.id}`), {
                                title: this.cardForm.title,
                                description: this.cardForm.description,
                                priority: this.cardForm.priority,
                                due_at: this.cardForm.due_at,
                            });

                            this.applyCardUpdated(response.data.data);

                            if (this.cardForm.assigned_user_id !== this.selectedCard.assigned_user_id) {
                                const assignResponse = await axios.post(this.apiBase(`/cards/${this.selectedCard.id}/assign`), {
                                    assigned_user_id: this.cardForm.assigned_user_id || null,
                                });
                                this.applyCardUpdated(assignResponse.data.data);
                            }
                        } finally {
                            this.loading = false;
                        }
                    },

                    async archiveSelectedCard() {
                        if (!this.selectedCard) {
                            return;
                        }

                        await axios.post(this.apiBase(`/cards/${this.selectedCard.id}/archive`));
                        this.applyCardArchived({ id: this.selectedCard.id, column_id: this.selectedColumnId });
                        this.closeCardModal();
                    },

                    async addComment(body) {
                        if (!this.selectedCard || !body.trim()) {
                            return;
                        }

                        const response = await axios.post(this.apiBase(`/cards/${this.selectedCard.id}/comments`), {
                            body,
                        });

                        this.selectedCardComments.unshift(response.data.data);
                    },

                    initEcho() {
                        if (!window.Echo) {
                            return;
                        }

                        window.Echo.private(`boards.${this.boardId}`)
                            .listen('.card.created', (event) => this.applyCardCreated(event.card))
                            .listen('.card.updated', (event) => this.applyCardUpdated(event.card))
                            .listen('.card.moved', (event) => this.applyCardMoved(event.card))
                            .listen('.card.archived', (event) => this.applyCardArchived(event.card))
                            .listen('.column.created', (event) => this.applyColumnCreated(event.column))
                            .listen('.column.updated', (event) => this.applyColumnUpdated(event.column))
                            .listen('.column.reordered', (event) => this.applyColumnReordered(event.columns))
                            .listen('.column.archived', (event) => this.applyColumnArchived(event.column))
                            .listen('.comment.created', (event) => this.applyCommentCreated(event.comment))
                            .listen('.activity.created', (event) => this.applyActivityCreated(event.activity));

                        window.Echo.join(`boards.${this.boardId}.presence`)
                            .here((users) => {
                                this.onlineUsers = users;
                            })
                            .joining((user) => {
                                if (!this.onlineUsers.find(item => Number(item.id) === Number(user.id))) {
                                    this.onlineUsers.push(user);
                                }
                            })
                            .leaving((user) => {
                                this.onlineUsers = this.onlineUsers.filter(item => Number(item.id) !== Number(user.id));
                            });
                    },

                    applyCardCreated(card) {
                        const column = this.findColumn(card.column_id);
                        if (!column) return;
                        if (!column.cards.find(item => Number(item.id) === Number(card.id))) {
                            column.cards.push(card);
                            this.sortCards(column);
                        }
                    },

                    applyCardUpdated(card) {
                        const located = this.findCard(card.id);
                        if (!located.card || !located.column) return;
                        Object.assign(located.card, card);
                        if (this.selectedCard && Number(this.selectedCard.id) === Number(card.id)) {
                            Object.assign(this.selectedCard, card);
                        }
                        this.sortCards(located.column);
                    },

                    applyCardMoved(card) {
                        let movedCard = null;

                        for (const column of this.columns) {
                            const index = column.cards.findIndex(item => Number(item.id) === Number(card.id));
                            if (index !== -1) {
                                movedCard = { ...column.cards[index], ...card };
                                column.cards.splice(index, 1);
                                break;
                            }
                        }

                        if (!movedCard) return;

                        const targetColumn = this.findColumn(card.column_id);
                        if (!targetColumn) return;

                        targetColumn.cards.push(movedCard);
                        this.sortCards(targetColumn);
                    },

                    applyCardArchived(card) {
                        for (const column of this.columns) {
                            const index = column.cards.findIndex(item => Number(item.id) === Number(card.id));
                            if (index !== -1) {
                                column.cards.splice(index, 1);
                            }
                        }
                    },

                    applyColumnCreated(column) {
                        if (!this.findColumn(column.id)) {
                            this.columns.push({ ...column, cards: [] });
                            this.sortColumns();
                            this.$nextTick(() => this.initCardSortables());
                        }
                    },

                    applyColumnUpdated(column) {
                        const existing = this.findColumn(column.id);
                        if (!existing) return;
                        Object.assign(existing, column);
                        this.sortColumns();
                    },

                    applyColumnReordered(columns) {
                        columns.forEach((payload) => {
                            const existing = this.findColumn(payload.id);
                            if (existing) {
                                existing.order_key = payload.order_key;
                                existing.updated_at = payload.updated_at;
                            }
                        });
                        this.sortColumns();
                    },

                    applyColumnArchived(column) {
                        this.columns = this.columns.filter(item => Number(item.id) !== Number(column.id));
                    },

                    applyCommentCreated(comment) {
                        if (this.selectedCard && Number(this.selectedCard.id) === Number(comment.card_id)) {
                            this.selectedCardComments.unshift(comment);
                        }
                    },

                    applyActivityCreated(activity) {
                        this.activities.unshift(activity);
                        this.activities = this.activities.slice(0, 50);
                    },

                    initColumnSortable() {
                        if (!this.canEdit) return;

                        this.$nextTick(() => {
                            const container = this.$refs.columnsContainer;
                            if (!container || container._sortable) return;

                            container._sortable = Sortable.create(container, {
                                animation: 180,
                                handle: '[data-column-handle]',
                                draggable: '[data-column-id]',
                                onEnd: async () => {
                                    const orderedIds = Array.from(container.querySelectorAll('[data-column-id]'))
                                        .map((element) => Number(element.dataset.columnId));
                                    await axios.post(this.apiBase('/columns/reorder'), { column_ids: orderedIds });
                                },
                            });
                        });
                    },

                    initCardSortables() {
                        if (!this.canEdit) return;

                        this.$nextTick(() => {
                            document.querySelectorAll('[data-cards-sortable]').forEach((container) => {
                                if (container._sortable) {
                                    container._sortable.destroy();
                                }

                                container._sortable = Sortable.create(container, {
                                    animation: 180,
                                    group: 'board-cards',
                                    draggable: '[data-card-id]',
                                    onEnd: async (event) => {
                                        const cardId = Number(event.item.dataset.cardId);
                                        const targetColumnId = Number(event.to.dataset.columnId);
                                        const orderedIds = Array.from(event.to.querySelectorAll('[data-card-id]'))
                                            .map((element) => Number(element.dataset.cardId));
                                        const newIndex = orderedIds.findIndex((id) => id === cardId);
                                        const targetColumn = this.findColumn(targetColumnId);
                                        if (!targetColumn) return;

                                        const sortedCards = [...targetColumn.cards].sort((a, b) => a.order_key.localeCompare(b.order_key));
                                        const previousCard = sortedCards[newIndex - 1] ?? null;
                                        const nextCard = sortedCards[newIndex] && Number(sortedCards[newIndex].id) !== cardId
                                            ? sortedCards[newIndex]
                                            : sortedCards[newIndex + 1] ?? null;
                                        const orderKey = this.resolveDropOrderKey(previousCard?.order_key ?? null, nextCard?.order_key ?? null);

                                        const located = this.findCard(cardId);
                                        if (!located.card) return;

                                        if (located.column) {
                                            located.column.cards = located.column.cards.filter((card) => Number(card.id) !== cardId);
                                        }

                                        targetColumn.cards.push({ ...located.card, column_id: targetColumnId, order_key: orderKey });
                                        this.sortCards(targetColumn);

                                        await axios.post(this.apiBase(`/cards/${cardId}/move`), {
                                            column_id: targetColumnId,
                                            order_key: orderKey,
                                        });
                                    },
                                });
                            });
                        });
                    },

                    resolveDropOrderKey(previous, next) {
                        if (previous && next) return this.generateOrderKeyBetween(previous, next);
                        if (previous) return this.generateOrderKeyAfter(previous);
                        if (next) return this.generateOrderKeyBefore(next);
                        return this.generateOrderKeyBetween(null, null);
                    },

                    generateOrderKeyBefore(right) {
                        return this.generateOrderKeyBetween(null, right);
                    },

                    generateOrderKeyAfter(left) {
                        return this.generateOrderKeyBetween(left, null);
                    },

                    generateOrderKeyBetween(left, right) {
                        const alphabet = '0123456789abcdefghijklmnopqrstuvwxyz';
                        const base = alphabet.length;
                        let prefix = '';
                        let index = 0;

                        const digitAt = (value, position, fallback) => {
                            if (!value || position >= value.length) return fallback;
                            const digit = alphabet.indexOf(value[position]);
                            return digit === -1 ? fallback : digit;
                        };

                        while (true) {
                            const leftDigit = digitAt(left, index, 0);
                            const rightDigit = digitAt(right, index, base - 1);

                            if (rightDigit - leftDigit > 1) {
                                const midpoint = Math.floor((leftDigit + rightDigit) / 2);
                                return `${prefix}${alphabet[midpoint]}`;
                            }

                            prefix += alphabet[leftDigit];
                            index++;
                        }
                    },
                };
            }
        </script>
    @endpush
</div>
