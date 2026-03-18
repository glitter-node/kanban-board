<div
    x-data="{ open: false, commentBody: '' }"
    x-init="
        window.addEventListener('board:card-modal-open', () => open = true);
        window.addEventListener('board:card-modal-close', () => open = false);
    "
>
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6">
        <div class="absolute inset-0 bg-canvas" @click="closeCardModal()"></div>

        <x-ui.card class="relative z-10 flex max-h-[85vh] w-full max-w-4xl flex-col overflow-hidden rounded-3xl p-0">
            <div class="ui-panel-header flex items-start justify-between gap-4 px-6 py-5">
                <div class="min-w-0">
                    <p class="ui-kicker">Card Detail</p>
                    <h2 class="mt-2 truncate text-xl font-semibold" x-text="selectedCard?.title || 'Card'"></h2>
                </div>

                <x-ui.button
                    type="button"
                    variant="icon"
                    size="sm"
                    aria-label="Close card details"
                    @click="closeCardModal()"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </x-ui.button>
            </div>

            <div class="grid min-h-0 flex-1 grid-cols-1 overflow-hidden lg:grid-cols-[minmax(0,1fr)_340px]">
                <div class="min-h-0 overflow-y-auto px-6 py-5">
                    <form class="space-y-5" @submit.prevent="saveCard()">
                        <div class="flex flex-wrap items-center gap-2">
                            <x-ui.badge tone="warning" x-show="selectedCard?.blocked">Blocked</x-ui.badge>
                            <x-ui.badge tone="error" x-show="selectedCard && cardIsStuck(selectedCard)">Stuck</x-ui.badge>
                            <x-ui.badge tone="info" x-show="selectedCard && isNextActionable(selectedCard)">Next pull</x-ui.badge>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium ui-meta">Title</label>
                            <x-ui.surface class="p-0">
                                <input type="text" x-model="cardForm.title" class="ui-input w-full rounded-xl border-0 bg-transparent px-4 py-3 text-sm outline-none">
                            </x-ui.surface>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium ui-meta">Description</label>
                            <x-ui.surface class="p-0">
                                <textarea x-model="cardForm.description" rows="5" class="ui-input w-full rounded-xl border-0 bg-transparent px-4 py-3 text-sm outline-none"></textarea>
                            </x-ui.surface>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium ui-meta">Priority</label>
                                <x-ui.surface class="p-0">
                                    <select x-model="cardForm.priority" class="ui-input w-full rounded-xl border-0 bg-transparent px-4 py-3 text-sm outline-none">
                                        <option :value="1">Low</option>
                                        <option :value="2">Medium</option>
                                        <option :value="3">High</option>
                                        <option :value="4">Critical</option>
                                    </select>
                                </x-ui.surface>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-medium ui-meta">Assignee</label>
                                <x-ui.surface class="p-0">
                                    <select x-model="cardForm.assigned_user_id" class="ui-input w-full rounded-xl border-0 bg-transparent px-4 py-3 text-sm outline-none">
                                        <option value="">Unassigned</option>
                                        <template x-for="user in users" :key="user.id">
                                            <option :value="user.id" x-text="user.name"></option>
                                        </template>
                                    </select>
                                </x-ui.surface>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-medium ui-meta">Due date</label>
                                <x-ui.surface class="p-0">
                                    <input type="date" x-model="cardForm.due_at" class="ui-input w-full rounded-xl border-0 bg-transparent px-4 py-3 text-sm outline-none">
                                </x-ui.surface>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-[180px_minmax(0,1fr)]">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium ui-meta">Flow state</label>
                                <x-ui.surface class="flex items-center gap-3 px-4 py-3">
                                    <input type="checkbox" x-model="cardForm.blocked" class="h-4 w-4 rounded border-border bg-input-background text-primary focus:ring-primary">
                                    <span class="text-sm">Blocked</span>
                                </x-ui.surface>
                            </div>

                            <div class="space-y-2" x-show="cardForm.blocked">
                                <label class="block text-sm font-medium ui-meta">Blocked reason</label>
                                <x-ui.surface class="p-0">
                                    <textarea x-model="cardForm.blocked_reason" rows="3" class="ui-input w-full rounded-xl border-0 bg-transparent px-4 py-3 text-sm outline-none" placeholder="What is preventing this card from moving?"></textarea>
                                </x-ui.surface>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <x-ui.button
                                type="button"
                                variant="danger"
                                size="lg"
                                x-show="canEdit"
                                class="rounded-2xl normal-case tracking-normal"
                                @click="archiveSelectedCard()"
                            >
                                Archive card
                            </x-ui.button>

                            <x-ui.button
                                type="submit"
                                variant="primary"
                                size="lg"
                                class="rounded-2xl normal-case tracking-normal"
                                :disabled="loading"
                            >
                                Save changes
                            </x-ui.button>
                        </div>
                    </form>
                </div>

                <x-ui.surface variant="elevated" class="rounded-none border-l border-t lg:border-t-0 px-6 py-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-sm font-semibold">Comments</h3>
                        <span class="ui-meta" x-text="selectedCardComments.length"></span>
                    </div>

                    <livewire:board.comment-list :key="'comment-list-'.$boardId" />

                    <div class="mt-4 space-y-3" x-show="canEdit">
                        <x-ui.surface class="p-0">
                            <textarea
                                x-model="commentBody"
                                rows="3"
                                placeholder="Write a comment..."
                                class="ui-input w-full rounded-xl border-0 bg-transparent px-4 py-3 text-sm outline-none"
                            ></textarea>
                        </x-ui.surface>

                        <x-ui.button
                            type="button"
                            variant="secondary"
                            size="lg"
                            class="w-full rounded-2xl normal-case tracking-normal"
                            @click="addComment(commentBody); commentBody = ''"
                        >
                            Add comment
                        </x-ui.button>
                    </div>
                </x-ui.surface>
            </div>
        </x-ui.card>
    </div>
</div>
