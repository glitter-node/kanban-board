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
                    {{-- Profile Information --}}
                    <div class="ui-panel p-4 sm:rounded-lg sm:p-8">
                        <div class="max-w-xl">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>

                    <x-layout.divider />

                    {{-- Theme Settings --}}
                    <div class="ui-panel p-4 sm:rounded-lg sm:p-8">
                        <div class="max-w-xl" x-data="{ theme: $store.theme.current }">
                            <section>
                                <header>
                                    <h2 class="text-lg font-medium text-foreground">Theme Settings</h2>
                                    <p class="mt-1 text-sm text-secondary">Choose a display theme.</p>
                                </header>

                                <x-layout.grid :lg="null" class="mt-6" md="2">
                                    <button @click="theme = 'light'; $store.theme.set('light')"
                                            class="focus-ring flex-1 rounded-lg border-2 p-4 transition-all bg-primary text-white"
                                            :class="theme === 'light' ? 'border-primary bg-primary text-primary-foreground' : 'border-border bg-muted text-foreground hover:border-muted-foreground'"
                                            role="radio" :aria-checked="theme === 'light'" tabindex="0">
                                        <div class="flex items-center gap-3">
                                            <svg class="h-6 w-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                            </svg>
                                            <span class="text-sm font-medium text-foreground text-secondary">Light</span>
                                        </div>
                                    </button>

                                    <button @click="theme = 'dark'; $store.theme.set('dark')"
                                            class="focus-ring flex-1 rounded-lg border-2 p-4 transition-all bg-primary text-white"
                                            :class="theme === 'dark' ? 'border-primary bg-primary text-primary-foreground' : 'border-border bg-muted text-foreground hover:border-muted-foreground'"
                                            role="radio" :aria-checked="theme === 'dark'" tabindex="0">
                                        <div class="flex items-center gap-3">
                                        <svg class="h-6 w-6 text-primary-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                                            </svg>
                                            <span class="text-sm font-medium text-foreground text-secondary">Dark</span>
                                        </div>
                                    </button>
                                </x-layout.grid>
                            </section>
                        </div>
                    </div>

                    {{-- Notification Settings --}}
                    <div class="ui-panel p-4 sm:rounded-lg sm:p-8">
                        <div class="max-w-xl" x-data="{
                            notifyAssignment: localStorage.getItem('notify_assignment') !== 'false',
                            notifyComment: localStorage.getItem('notify_comment') !== 'false',
                            notifyDueDate: localStorage.getItem('notify_due') !== 'false',
                            save(key, val) { localStorage.setItem(key, val); }
                        }">
                            <section>
                                <header>
                                    <h2 class="text-lg font-medium text-foreground">Notification Settings</h2>
                                    <p class="mt-1 text-sm text-secondary">Choose which notifications you want to receive.</p>
                                </header>

                                <x-layout.stack class="mt-6" space="4">
                                    <label bg-surface text-secondary border border-border class="flex items-center gap-3 cursor-pointer">
                                        <input type="checkbox" x-model="notifyAssignment" @change="save('notify_assignment', notifyAssignment)"
                                               class="focus-ring rounded border-border bg-muted text-primary-foreground">
                                        <div>
                                            <span class="text-sm font-medium text-foreground text-secondary">Card Assignment Notifications</span>
                                            <p class="text-xs text-secondary">When a card is assigned to me</p>
                                        </div>
                                    </label bg-surface text-secondary border border-border>

                                    <label bg-surface text-secondary border border-border class="flex items-center gap-3 cursor-pointer">
                                        <input type="checkbox" x-model="notifyComment" @change="save('notify_comment', notifyComment)"
                                               class="focus-ring rounded border-border bg-muted text-primary-foreground">
                                        <div>
                                            <span class="text-sm font-medium text-foreground text-secondary">Comment Notifications</span>
                                            <p class="text-xs text-secondary">When someone comments on my card</p>
                                        </div>
                                    </label bg-surface text-secondary border border-border>

                                    <label bg-surface text-secondary border border-border class="flex items-center gap-3 cursor-pointer">
                                        <input type="checkbox" x-model="notifyDueDate" @change="save('notify_due', notifyDueDate)"
                                               class="focus-ring rounded border-border bg-muted text-primary-foreground">
                                        <div>
                                            <span class="text-sm font-medium text-foreground text-secondary">Due Date Notifications</span>
                                            <p class="text-xs text-secondary">When a card due date is approaching</p>
                                        </div>
                                    </label bg-surface text-secondary border border-border>
                                </x-layout.stack>
                            </section>
                        </div>
                    </div>

                    <x-layout.divider />

                    {{-- Password --}}
                    <div class="ui-panel p-4 sm:rounded-lg sm:p-8">
                        <div class="max-w-xl">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>

                    {{-- Delete Account --}}
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
