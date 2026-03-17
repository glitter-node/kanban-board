<div
    x-data="{ open: false, commentBody: '' }"
    x-init="
        window.addEventListener('board:card-modal-open', () => open = true);
        window.addEventListener('board:card-modal-close', () => open = false);
    "
>
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-canvas/70 px-4 py-6 backdrop-blur-sm">
        <div class="absolute inset-0" @click="closeCardModal()"></div>

        <div class="ui-panel-elevated relative z-10 flex max-h-[85vh] w-full max-w-4xl flex-col overflow-hidden rounded-3xl p-0 shadow-2xl">
            <div class="ui-panel-header flex items-start justify-between gap-4 px-6 py-5">
                <div class="min-w-0">
                    <p class="ui-kicker">Card Detail</p>
                    <h2 class="mt-2 truncate text-xl font-semibold text-ui-text-primary" x-text="selectedCard?.title || 'Card'"></h2>
                </div>

                <button
                    type="button"
                    class="btn-icon"
                    @click="closeCardModal()"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="grid min-h-0 flex-1 grid-cols-1 overflow-hidden lg:grid-cols-[minmax(0,1fr)_340px]">
                <div class="min-h-0 overflow-y-auto px-6 py-5">
                    <form class="space-y-5" @submit.prevent="saveCard()">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-ui-text-secondary">Title</label>
                            <input type="text" x-model="cardForm.title" class="ui-input w-full rounded-2xl px-4 py-3 text-sm">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-ui-text-secondary">Description</label>
                            <textarea x-model="cardForm.description" rows="5" class="ui-input w-full rounded-2xl px-4 py-3 text-sm"></textarea>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-ui-text-secondary">Priority</label>
                                <select x-model="cardForm.priority" class="ui-input w-full rounded-2xl px-4 py-3 text-sm">
                                    <option :value="1">Low</option>
                                    <option :value="2">Medium</option>
                                    <option :value="3">High</option>
                                    <option :value="4">Critical</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-ui-text-secondary">Assignee</label>
                                <select x-model="cardForm.assigned_user_id" class="ui-input w-full rounded-2xl px-4 py-3 text-sm">
                                    <option value="">Unassigned</option>
                                    <template x-for="user in users" :key="user.id">
                                        <option :value="user.id" x-text="user.name"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-ui-text-secondary">Due date</label>
                                <input type="date" x-model="cardForm.due_at" class="ui-input w-full rounded-2xl px-4 py-3 text-sm">
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <button type="button" x-show="canEdit" class="btn-danger rounded-2xl px-4 py-3 text-sm font-medium normal-case tracking-normal" @click="archiveSelectedCard()">
                                Archive card
                            </button>

                            <button type="submit" class="btn-primary rounded-2xl px-5 py-3 text-sm normal-case tracking-normal" :disabled="loading">
                                Save changes
                            </button>
                        </div>
                    </form>
                </div>

                <div class="border-t border-border bg-section/60 px-6 py-5 lg:border-l lg:border-t-0">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-ui-text-primary">Comments</h3>
                        <span class="ui-meta" x-text="selectedCardComments.length"></span>
                    </div>

                    <livewire:board.comment-list :key="'comment-list-'.$boardId" />

                    <div class="mt-4 space-y-3" x-show="canEdit">
                        <textarea x-model="commentBody" rows="3" placeholder="Write a comment..." class="ui-input w-full rounded-2xl px-4 py-3 text-sm"></textarea>

                        <button type="button" class="btn-secondary w-full rounded-2xl px-4 py-3 text-sm normal-case tracking-normal" @click="addComment(commentBody); commentBody = ''">
                            Add comment
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
