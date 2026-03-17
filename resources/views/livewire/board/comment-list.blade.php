<div class="space-y-3">
    <template x-if="selectedCardCommentsLoading">
        <x-layout.stack space="3">
            <x-ui.state.loading label="Loading comments" />
            <x-ui.state.skeleton :lines="3" :avatar="true" />
        </x-layout.stack>
    </template>

    <template x-if="!selectedCardCommentsLoading && selectedCardCommentsError">
        <x-ui.state.error
            title="Comments unavailable"
            retryLabel="Retry"
            @click="loadComments(selectedCard.id)"
        >
            <span x-text="selectedCardCommentsError"></span>
        </x-ui.state.error>
    </template>

    <template x-if="!selectedCardCommentsLoading && !selectedCardCommentsError && selectedCardComments.length === 0">
        <x-ui.state.empty
            title="No comments yet"
            description="Start the discussion on this card to keep work and context together."
        />
    </template>

    <template x-for="comment in selectedCardComments" :key="comment.id">
        <x-ui.card class="px-4 py-3">
            <div class="flex items-center gap-3">
                <x-ui.avatar x-text="initials(comment.author_name || currentUserName)"></x-ui.avatar>
                <div>
                    <p class="text-sm font-medium text-foreground" x-text="comment.author_name || currentUserName"></p>
                    <p class="ui-meta" x-text="comment.created_at ? new Date(comment.created_at).toLocaleString() : ''"></p>
                </div>
            </div>
            <p class="mt-3 whitespace-pre-wrap text-sm ui-subtle" x-text="comment.body"></p>
        </x-ui.card>
    </template>
</div>
