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
            editingCardId: null,
            inlineCardTitle: '',
            pendingArchive: null,
            cardForm: {
                id: null,
                title: '',
                description: '',
                priority: 2,
                assigned_user_id: null,
                blocked: false,
                blocked_reason: '',
                due_at: null,
            },
            loading: false,

            init() {
                try {
                    this.normalizeColumns();
                    this.initColumnSortable();
                    this.initCardSortables();
                    this.initEcho();
                    this.registerKeyboardShortcuts();
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
                this.recalculateFlowState();
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

                this.recalculateFlowState();
                this.$nextTick(() => this.initCardSortables());
            },

            queueToast(message, tone = 'info', options = {}) {
                const id = Date.now() + Math.floor(Math.random() * 1000);
                const toast = {
                    id,
                    message,
                    tone,
                    actionLabel: options.actionLabel ?? null,
                    actionType: options.actionType ?? null,
                    actionPayload: options.actionPayload ?? null,
                };

                this.toasts.push(toast);
                toast.timeoutId = window.setTimeout(() => {
                    this.dismissToast(id);
                }, options.duration ?? 3200);

                return id;
            },

            dismissToast(id) {
                const toast = this.toasts.find((item) => item.id === id);
                if (toast?.timeoutId) {
                    window.clearTimeout(toast.timeoutId);
                }

                this.toasts = this.toasts.filter((item) => item.id !== id);
            },

            async handleToastAction(id) {
                const toast = this.toasts.find((item) => item.id === id);
                if (!toast?.actionType) {
                    return;
                }

                this.dismissToast(id);

                if (toast.actionType === 'undo-move') {
                    await this.undoMove(toast.actionPayload);
                    return;
                }

                if (toast.actionType === 'undo-archive') {
                    this.undoArchive(toast.actionPayload);
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

            recalculateFlowState() {
                this.columns.forEach((column) => {
                    column.current_wip = column.cards.filter((card) => card.status !== 'archived').length;
                });
            },

            boardAverageCycleTime() {
                const completedCards = this.columns
                    .flatMap((column) => column.cards)
                    .filter((card) => card.created_at && card.moved_to_done_at);

                if (!completedCards.length) {
                    return this.board.metrics?.average_cycle_time_hours ?? null;
                }

                const totalHours = completedCards.reduce((sum, card) => {
                    return sum + ((new Date(card.moved_to_done_at).getTime() - new Date(card.created_at).getTime()) / 3600000);
                }, 0);

                return Math.round((totalHours / completedCards.length) * 100) / 100;
            },

            completedThisWeek() {
                const startOfWeek = new Date();
                startOfWeek.setDate(startOfWeek.getDate() - startOfWeek.getDay());
                startOfWeek.setHours(0, 0, 0, 0);

                return this.columns
                    .flatMap((column) => column.cards)
                    .filter((card) => card.moved_to_done_at && new Date(card.moved_to_done_at) >= startOfWeek)
                    .length;
            },

            blockedCardsCount() {
                return this.columns
                    .flatMap((column) => column.cards)
                    .filter((card) => card.blocked)
                    .length;
            },

            columnUsagePercent(column) {
                if (!column.wip_limit) {
                    return 0;
                }

                return Math.min(100, Math.round(((column.current_wip || 0) / column.wip_limit) * 100));
            },

            columnUsageBarClass(column) {
                const percent = this.columnUsagePercent(column);

                if (percent <= 0) return 'kanban-wip-0';
                if (percent <= 10) return 'kanban-wip-10';
                if (percent <= 20) return 'kanban-wip-20';
                if (percent <= 30) return 'kanban-wip-30';
                if (percent <= 40) return 'kanban-wip-40';
                if (percent <= 50) return 'kanban-wip-50';
                if (percent <= 60) return 'kanban-wip-60';
                if (percent <= 70) return 'kanban-wip-70';
                if (percent <= 80) return 'kanban-wip-80';
                if (percent <= 90) return 'kanban-wip-90';
                return 'kanban-wip-100';
            },

            columnOverLimit(column) {
                return Boolean(column.wip_limit) && (column.current_wip || 0) > column.wip_limit;
            },

            columnAtLimit(column) {
                return Boolean(column.wip_limit) && (column.current_wip || 0) >= column.wip_limit;
            },

            cardIsStuck(card) {
                const reference = card.updated_at || card.created_at;

                if (!reference || ['done', 'archived'].includes(card.status)) {
                    return false;
                }

                return ((Date.now() - new Date(reference).getTime()) / 3600000) >= 24;
            },

            nextActionableCardId() {
                for (const column of this.columns) {
                    if (['done', 'completed'].includes((column.type || '').toLowerCase())) {
                        continue;
                    }

                    const candidate = [...column.cards]
                        .sort((a, b) => a.order_key.localeCompare(b.order_key))
                        .find((card) => !card.blocked && card.status !== 'archived');

                    if (candidate) {
                        return Number(candidate.id);
                    }
                }

                return null;
            },

            isNextActionable(card) {
                return Number(card.id) === Number(this.nextActionableCardId());
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

            registerKeyboardShortcuts() {
                window.addEventListener('keydown', (event) => {
                    if (event.defaultPrevented) {
                        return;
                    }

                    const tagName = event.target instanceof HTMLElement ? event.target.tagName : '';
                    const isTyping = ['INPUT', 'TEXTAREA', 'SELECT'].includes(tagName);

                    if ((event.key === 'Escape') && this.selectedCard) {
                        event.preventDefault();
                        this.closeCardModal();
                        return;
                    }

                    if ((event.metaKey || event.ctrlKey) && event.key === 'Enter' && this.selectedCard) {
                        event.preventDefault();
                        void this.saveCard();
                        return;
                    }

                    if (!isTyping && !this.selectedCard && (event.key === 'n' || event.key === 'N')) {
                        const nextCardId = this.nextActionableCardId();
                        if (nextCardId) {
                            event.preventDefault();
                            this.openCard(nextCardId);
                        }
                    }
                });
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
                    blocked: !!card.blocked,
                    blocked_reason: card.blocked_reason || '',
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
                    blocked: false,
                    blocked_reason: '',
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
                    if (this.cardForm.blocked && !this.cardForm.blocked_reason.trim()) {
                        this.queueToast('A blocked card requires a reason.', 'error');
                        return;
                    }

                    const response = await window.axios.patch(this.apiBase(`/cards/${this.selectedCard.id}`), {
                        title: this.cardForm.title,
                        description: this.cardForm.description,
                        priority: this.cardForm.priority,
                        blocked: this.cardForm.blocked,
                        blocked_reason: this.cardForm.blocked ? this.cardForm.blocked_reason : null,
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

            startInlineEdit(card) {
                if (!this.canEdit) {
                    return;
                }

                this.editingCardId = Number(card.id);
                this.inlineCardTitle = card.title || '';
            },

            cancelInlineEdit() {
                this.editingCardId = null;
                this.inlineCardTitle = '';
            },

            async saveInlineCardTitle(cardId) {
                const title = this.inlineCardTitle.trim();
                if (!title) {
                    this.queueToast('Card title cannot be empty.', 'error');
                    return;
                }

                try {
                    const response = await window.axios.patch(this.apiBase(`/cards/${cardId}`), {
                        title,
                    });

                    this.applyCardUpdated(response.data.data);
                    this.cancelInlineEdit();
                } catch (error) {
                    console.error(error);
                    this.queueToast(error?.response?.data?.message || 'Card title could not be updated.', 'error');
                }
            },

            async quickAssignToMe(cardId) {
                try {
                    const response = await window.axios.post(this.apiBase(`/cards/${cardId}/assign`), {
                        assigned_user_id: this.currentUserId,
                    });

                    this.applyCardUpdated(response.data.data);
                    this.queueToast('Card assigned to you.', 'success');
                } catch (error) {
                    console.error(error);
                    this.queueToast(error?.response?.data?.message || 'Assignment failed.', 'error');
                }
            },

            async archiveSelectedCard() {
                if (!this.selectedCard) {
                    return;
                }

                this.archiveCardWithUndo(this.selectedCard.id, this.selectedColumnId);
                this.closeCardModal();
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

            archiveCardWithUndo(cardId, columnId = null) {
                const snapshot = this.cloneColumns();
                const located = this.findCard(cardId);
                if (!located.card || !located.column) {
                    return;
                }

                const resolvedColumnId = columnId ?? located.column.id;

                this.applyCardArchived({ id: cardId, column_id: resolvedColumnId });

                const timeoutId = window.setTimeout(async () => {
                    this.pendingArchive = null;

                    try {
                        await window.axios.post(this.apiBase(`/cards/${cardId}/archive`));
                        this.queueToast('Card archived.', 'info');
                    } catch (error) {
                        console.error(error);
                        this.restoreColumns(snapshot);
                        this.queueToast(error?.response?.data?.message || 'Card could not be archived.', 'error');
                    }
                }, 2600);

                this.pendingArchive = { cardId, snapshot, timeoutId };

                this.queueToast('Card moved to archive.', 'info', {
                    actionLabel: 'Undo',
                    actionType: 'undo-archive',
                    actionPayload: { cardId },
                    duration: 2600,
                });
            },

            undoArchive(payload) {
                if (!this.pendingArchive || Number(this.pendingArchive.cardId) !== Number(payload?.cardId)) {
                    return;
                }

                window.clearTimeout(this.pendingArchive.timeoutId);
                this.restoreColumns(this.pendingArchive.snapshot);
                this.pendingArchive = null;
                this.queueToast('Archive undone.', 'success');
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
                    this.recalculateFlowState();
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
                this.recalculateFlowState();
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
                this.recalculateFlowState();

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
                this.recalculateFlowState();
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
                if (
                    targetColumn.wip_limit
                    && Number(sourceColumn.id) !== Number(targetColumn.id)
                    && (targetColumn.current_wip || targetColumn.cards.length) >= targetColumn.wip_limit
                ) {
                    this.queueToast('This move would exceed the destination column WIP limit.', 'error');
                    return null;
                }

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
                this.recalculateFlowState();

                if (this.selectedCard && Number(this.selectedCard.id) === Number(cardId)) {
                    Object.assign(this.selectedCard, updatedCard);
                    this.selectedColumnId = targetColumnId;
                }

                return { snapshot, orderKey, sourceColumnId: sourceColumn.id };
            },

            buildUndoMovePayload(snapshot, cardId) {
                const sortedColumns = snapshot
                    .map((column) => ({
                        ...column,
                        cards: [...column.cards].sort((a, b) => a.order_key.localeCompare(b.order_key)),
                    }))
                    .sort((a, b) => a.order_key.localeCompare(b.order_key));

                for (const column of sortedColumns) {
                    const index = column.cards.findIndex((card) => Number(card.id) === Number(cardId));
                    if (index !== -1) {
                        return {
                            cardId,
                            targetColumnId: column.id,
                            targetIndex: index,
                        };
                    }
                }

                return null;
            },

            async undoMove(payload) {
                if (!payload) {
                    return;
                }

                await this.persistOptimisticMove(
                    payload.cardId,
                    payload.targetColumnId,
                    payload.targetIndex,
                    null,
                    { allowUndoToast: false },
                );
            },

            async persistOptimisticMove(cardId, targetColumnId, targetIndex, fromColumnId = null, options = {}) {
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

                    if (options.allowUndoToast !== false) {
                        const undoPayload = this.buildUndoMovePayload(payload.snapshot, cardId);
                        if (undoPayload) {
                            this.queueToast('Card moved.', 'success', {
                                actionLabel: 'Undo',
                                actionType: 'undo-move',
                                actionPayload: undoPayload,
                                duration: 3600,
                            });
                        }
                    }
                } catch (error) {
                    console.error(error);
                    this.restoreColumns(payload.snapshot);
                    this.queueToast(error?.response?.data?.message || 'Card move failed. Position restored.', 'error');
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
