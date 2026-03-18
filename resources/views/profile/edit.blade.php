<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-foreground">
            {{ __('Settings') }}
        </h2>
    </x-slot>

    <x-layout.page as="div">
        <x-layout.section spacing="sm">
            <x-layout.container>
                <x-layout.stack space="6">
                    <div class="ui-panel p-4 sm:rounded-lg sm:p-8">
                        <div class="max-w-xl">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>

                    <x-layout.divider />

                    <div
                        class="ui-panel p-4 sm:rounded-lg sm:p-8"
                        x-data="{
                            notifyAssignment: localStorage.getItem('notify_assignment') !== 'false',
                            notifyComment: localStorage.getItem('notify_comment') !== 'false',
                            notifyDueDate: localStorage.getItem('notify_due') !== 'false',
                            save(key, val) { localStorage.setItem(key, val); }
                        }"
                    >
                        <div class="max-w-xl">
                            <section>
                                <header>
                                    <h2 class="text-lg font-medium text-foreground">Notification Settings</h2>
                                    <p class="mt-1 text-sm text-secondary">Choose which notifications you want to receive.</p>
                                </header>

                                <x-layout.stack class="mt-6" space="4">
                                    <label class="flex cursor-pointer items-center gap-3">
                                        <input type="checkbox" x-model="notifyAssignment" @change="save('notify_assignment', notifyAssignment)" class="focus-ring rounded border-border bg-muted text-primary-foreground">
                                        <div>
                                            <span class="text-sm font-medium text-foreground text-secondary">Card Assignment Notifications</span>
                                            <p class="text-xs text-secondary">When a card is assigned to me</p>
                                        </div>
                                    </label>

                                    <label class="flex cursor-pointer items-center gap-3">
                                        <input type="checkbox" x-model="notifyComment" @change="save('notify_comment', notifyComment)" class="focus-ring rounded border-border bg-muted text-primary-foreground">
                                        <div>
                                            <span class="text-sm font-medium text-foreground text-secondary">Comment Notifications</span>
                                            <p class="text-xs text-secondary">When someone comments on my card</p>
                                        </div>
                                    </label>

                                    <label class="flex cursor-pointer items-center gap-3">
                                        <input type="checkbox" x-model="notifyDueDate" @change="save('notify_due', notifyDueDate)" class="focus-ring rounded border-border bg-muted text-primary-foreground">
                                        <div>
                                            <span class="text-sm font-medium text-foreground text-secondary">Due Date Notifications</span>
                                            <p class="text-xs text-secondary">When a card due date is approaching</p>
                                        </div>
                                    </label>
                                </x-layout.stack>
                            </section>
                        </div>
                    </div>

                    <x-layout.divider />

                    <div class="ui-panel p-4 sm:rounded-lg sm:p-8">
                        <div class="max-w-xl">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>

                    <div class="ui-panel p-4 sm:rounded-lg sm:p-8">
                        <div class="max-w-xl">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div>
                </x-layout.stack>
            </x-layout.container>
        </x-layout.section>
    </x-layout.page>
</x-app-layout>
