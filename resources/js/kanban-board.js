import Sortable from 'sortablejs';

const registerKanbanBoard = () => {
    window.kanbanBoardView = function kanbanBoardView(config) {
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
            actionError: null,
            toasts: [],
            selectedCard: null,
            selectedColumnId: null,
            selectedCardComments: [],
            selectedCardCommentsLoading: false,
            selectedCardCommentsError: null,
            pendingColumnReorder: null,
            activeDropPanel: null,
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
                    this.normalizeColumns();
                    this.initColumnSortable();
                    this.initCardSortables();
                    this.initEcho();
                    window.trackEvent?.('board_viewed', {
                        board_id: this.boardId,
                        columns_count: this.columns.length,
                    });
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

            normalizeColumns() {
                this.columns = [...this.columns]
                    .map((column) => ({
                        ...column,
                        cards: [...(column.cards || [])].sort((a, b) => a.order_key.localeCompare(b.order_key)),
                    }))
                    .sort((a, b) => a.order_key.localeCompare(b.order_key));
            },

            cloneColumns() {
                return this.columns.map((column) => ({
                    ...column,
                    cards: column.cards.map((card) => ({ ...card })),
                }));
            },

            restoreColumns(columns) {
                this.columns = columns.map((column) => ({
                    ...column,
                    cards: [...column.cards].sort((a, b) => a.order_key.localeCompare(b.order_key)),
                })).sort((a, b) => a.order_key.localeCompare(b.order_key));

                this.$nextTick(() => this.initCardSortables());
            },

            queueToast(message, tone = 'info') {
                const id = Date.now() + Math.floor(Math.random() * 1000);
                this.toasts.push({ id, message, tone });
                window.setTimeout(() => {
                    this.toasts = this.toasts.filter((toast) => toast.id !== id);
                }, 3200);
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

            findColumnIndex(columnId) {
                return this.columns.findIndex(column => Number(column.id) === Number(columnId));
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
                window.trackEvent?.('modal_opened', {
                    board_id: this.boardId,
                    card_id: card.id,
                    column_id: column.id,
                });
                window.dispatchEvent(new CustomEvent('board:card-modal-open'));
            },

            closeCardModal() {
                if (this.selectedCard) {
                    window.trackEvent?.('modal_closed', {
                        board_id: this.boardId,
                        card_id: this.selectedCard.id,
                        column_id: this.selectedColumnId,
                    });
                }

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
                    const response = await window.axios.get(this.apiBase(`/cards/${cardId}/comments`));
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
                    const response = await window.axios.patch(this.apiBase(`/cards/${this.selectedCard.id}`), {
                        title: this.cardForm.title,
                        description: this.cardForm.description,
                        priority: this.cardForm.priority,
                        due_at: this.cardForm.due_at,
                    });

                    this.applyCardUpdated(response.data.data);

                    if (this.cardForm.assigned_user_id !== this.selectedCard.assigned_user_id) {
                        const assignResponse = await window.axios.post(this.apiBase(`/cards/${this.selectedCard.id}/assign`), {
                            assigned_user_id: this.cardForm.assigned_user_id || null,
                        });
                        this.applyCardUpdated(assignResponse.data.data);
                    }
                } catch (error) {
                    console.error(error);
                    this.queueToast('Card changes could not be saved.', 'error');
                } finally {
                    this.loading = false;
                }
            },

            async archiveSelectedCard() {
                if (!this.selectedCard) {
                    return;
                }

                try {
                    await window.axios.post(this.apiBase(`/cards/${this.selectedCard.id}/archive`));
                    this.applyCardArchived({ id: this.selectedCard.id, column_id: this.selectedColumnId });
                    this.closeCardModal();
                    this.queueToast('Card archived.', 'info');
                } catch (error) {
                    console.error(error);
                    this.queueToast('Card could not be archived.', 'error');
                }
            },

            async addComment(body) {
                if (!this.selectedCard || !body.trim()) {
                    return;
                }

                try {
                    const response = await window.axios.post(this.apiBase(`/cards/${this.selectedCard.id}/comments`), {
                        body,
                    });

                    this.selectedCardComments.unshift(response.data.data);
                } catch (error) {
                    console.error(error);
                    this.queueToast('Comment could not be added.', 'error');
                }
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
                    this.queueToast('A new card was added.', 'info');
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

                if (this.selectedCard && Number(this.selectedCard.id) === Number(card.id)) {
                    this.selectedColumnId = card.column_id;
                    Object.assign(this.selectedCard, movedCard);
                }
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

            setDropTarget(panel) {
                if (this.activeDropPanel && this.activeDropPanel !== panel) {
                    this.activeDropPanel.classList.remove('drop-target');
                }

                this.activeDropPanel = panel;

                if (panel) {
                    panel.classList.add('drop-target');
                }
            },

            clearDropTarget() {
                if (this.activeDropPanel) {
                    this.activeDropPanel.classList.remove('drop-target');
                }

                this.activeDropPanel = null;
            },

            scheduleColumnReorder(columnIds) {
                if (this.pendingColumnReorder) {
                    window.clearTimeout(this.pendingColumnReorder);
                }

                this.pendingColumnReorder = window.setTimeout(async () => {
                    try {
                        await window.axios.post(this.apiBase('/columns/reorder'), { column_ids: columnIds });
                    } catch (error) {
                        console.error(error);
                        this.queueToast('Column order could not be saved.', 'error');
                    }
                }, 120);
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
                        ghostClass: 'drop-preview',
                        chosenClass: 'drag-active',
                        dragClass: 'drag-active',
                        onEnd: async () => {
                            const orderedIds = Array.from(container.querySelectorAll('[data-column-id]'))
                                .map((element) => Number(element.dataset.columnId));
                            this.scheduleColumnReorder(orderedIds);
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
                            handle: '[data-card-handle]',
                            draggable: '[data-card-id]',
                            ghostClass: 'drop-preview',
                            chosenClass: 'drag-active',
                            dragClass: 'drag-active',
                            onStart: (event) => {
                                event.item.classList.add('drag-active');
                                this.setDropTarget(event.from.closest('[data-column-panel]'));
                                window.trackEvent?.('drag_started', {
                                    board_id: this.boardId,
                                    card_id: Number(event.item.dataset.cardId),
                                    from_column_id: Number(event.from.dataset.columnId),
                                });
                            },
                            onMove: (event) => {
                                this.setDropTarget(event.to.closest('[data-column-panel]'));
                            },
                            onEnd: async (event) => {
                                event.item.classList.remove('drag-active');
                                this.clearDropTarget();

                                const cardId = Number(event.item.dataset.cardId);
                                const targetColumnId = Number(event.to.dataset.columnId);
                                const orderedIds = Array.from(event.to.querySelectorAll('[data-card-id]'))
                                    .map((element) => Number(element.dataset.cardId));
                                const newIndex = orderedIds.findIndex((id) => id === cardId);

                                await this.persistOptimisticMove(
                                    cardId,
                                    targetColumnId,
                                    newIndex,
                                    Number(event.from.dataset.columnId),
                                );
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

            optimisticMoveCard(cardId, targetColumnId, targetIndex) {
                const snapshot = this.cloneColumns();
                const located = this.findCard(cardId);
                const targetColumn = this.findColumn(targetColumnId);

                if (!located.card || !located.column || !targetColumn) {
                    return null;
                }

                const sourceColumn = located.column;
                const sourceCards = [...sourceColumn.cards].sort((a, b) => a.order_key.localeCompare(b.order_key));
                const targetCards = sourceColumn.id === targetColumn.id
                    ? sourceCards
                    : [...targetColumn.cards].sort((a, b) => a.order_key.localeCompare(b.order_key));

                const sourceIndex = sourceCards.findIndex((card) => Number(card.id) === Number(cardId));
                if (sourceIndex === -1) {
                    return null;
                }

                const [movedCard] = sourceCards.splice(sourceIndex, 1);

                let insertionIndex = Math.max(0, Math.min(targetIndex, targetCards.length));
                if (sourceColumn.id === targetColumn.id) {
                    insertionIndex = Math.max(0, Math.min(targetIndex, targetCards.length));
                }

                targetCards.splice(insertionIndex, 0, movedCard);

                const previousCard = targetCards[insertionIndex - 1] ?? null;
                const nextCard = targetCards[insertionIndex + 1] ?? null;
                const orderKey = this.resolveDropOrderKey(previousCard?.order_key ?? null, nextCard?.order_key ?? null);

                const updatedCard = {
                    ...movedCard,
                    column_id: targetColumnId,
                    order_key: orderKey,
                };

                targetCards[insertionIndex] = updatedCard;

                sourceColumn.cards = sourceColumn.id === targetColumn.id ? targetCards : sourceCards;
                targetColumn.cards = targetCards;

                this.sortCards(sourceColumn);
                if (sourceColumn.id !== targetColumn.id) {
                    this.sortCards(targetColumn);
                }

                if (this.selectedCard && Number(this.selectedCard.id) === Number(cardId)) {
                    Object.assign(this.selectedCard, updatedCard);
                    this.selectedColumnId = targetColumnId;
                }

                return { snapshot, orderKey, sourceColumnId: sourceColumn.id };
            },

            async persistOptimisticMove(cardId, targetColumnId, targetIndex, fromColumnId = null) {
                const payload = this.optimisticMoveCard(cardId, targetColumnId, targetIndex);
                if (!payload) {
                    return;
                }

                try {
                    await window.axios.post(this.apiBase(`/cards/${cardId}/move`), {
                        column_id: targetColumnId,
                        order_key: payload.orderKey,
                    });
                    window.trackEvent?.('drag_completed', {
                        board_id: this.boardId,
                        card_id: cardId,
                        from_column_id: fromColumnId ?? payload.sourceColumnId,
                        to_column_id: targetColumnId,
                        position: targetIndex,
                    });
                } catch (error) {
                    console.error(error);
                    this.restoreColumns(payload.snapshot);
                    this.queueToast('Card move failed. Position restored.', 'error');
                    window.trackEvent?.('ux_issue_detected', {
                        board_id: this.boardId,
                        issue: 'drag_failed',
                        card_id: cardId,
                        from_column_id: fromColumnId ?? payload.sourceColumnId,
                        to_column_id: targetColumnId,
                    });
                }
            },

            async moveCardVertically(cardId, columnId, offset) {
                const column = this.findColumn(columnId);
                if (!column) return;

                const sortedCards = [...column.cards].sort((a, b) => a.order_key.localeCompare(b.order_key));
                const currentIndex = sortedCards.findIndex((card) => Number(card.id) === Number(cardId));
                if (currentIndex === -1) return;

                const nextIndex = currentIndex + offset;
                if (nextIndex < 0 || nextIndex >= sortedCards.length) return;

                window.trackEvent?.('card_move_button_used', {
                    board_id: this.boardId,
                    card_id: cardId,
                    from_column_id: columnId,
                    to_column_id: columnId,
                    direction: offset > 0 ? 'down' : 'up',
                });

                await this.persistOptimisticMove(cardId, columnId, nextIndex, columnId);
            },

            async moveCardHorizontally(cardId, offset) {
                const located = this.findCard(cardId);
                if (!located.card || !located.column) return;

                const currentColumnIndex = this.findColumnIndex(located.column.id);
                if (currentColumnIndex === -1) return;

                const targetColumn = this.columns[currentColumnIndex + offset] ?? null;
                if (!targetColumn) return;

                window.trackEvent?.('card_move_button_used', {
                    board_id: this.boardId,
                    card_id: cardId,
                    from_column_id: located.column.id,
                    to_column_id: targetColumn.id,
                    direction: offset > 0 ? 'right' : 'left',
                });

                await this.persistOptimisticMove(cardId, targetColumn.id, targetColumn.cards.length, located.column.id);
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
    };
};

registerKanbanBoard();
