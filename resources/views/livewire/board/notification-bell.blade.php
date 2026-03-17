<div
    x-data="notificationBell({
        userId: @js($userId),
        initialNotifications: @js($initialNotifications),
    })"
    x-init="init()"
    class="relative"
>
    <x-ui.tooltip text="Notifications">
        <x-slot name="trigger">
            <x-ui.button
                type="button"
                variant="icon"
                size="md"
                class="relative"
                @click="open = !open"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span x-show="unreadCount > 0" x-cloak class="absolute -right-1 -top-1 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-error px-1 text-[10px] font-semibold text-ui-text-primary" x-text="unreadCount"></span>
            </x-ui.button>
        </x-slot>
    </x-ui.tooltip>

    <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 z-50 mt-3 w-96 overflow-hidden">
        <div class="ui-panel rounded-3xl">
            <div class="ui-panel-header">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="ui-kicker">Notifications</p>
                        <h2 class="mt-1 ui-title">Inbox</h2>
                    </div>
                    <x-ui.badge x-text="unreadCount + ' unread'"></x-ui.badge>
                </div>
            </div>

            <div class="max-h-[24rem] overflow-y-auto">
                <template x-if="notifications.length === 0">
                    <div class="px-5 py-10 text-center text-sm ui-muted">No notifications yet.</div>
                </template>

                <template x-for="notification in notifications" :key="notification.id">
                    <article class="border-b border-border px-5 py-4 transition last:border-b-0" :class="notification.read_at ? 'bg-surface' : 'bg-white'">
                        <div class="flex items-start gap-3">
                            <x-ui.avatar class="mt-1">
                                <span x-text="notification.type.split('.').pop().charAt(0).toUpperCase()"></span>
                            </x-ui.avatar>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-ui-text-primary" x-text="notification.payload?.title || notification.type"></p>
                                <p class="mt-1 ui-meta" x-text="notification.created_at ? new Date(notification.created_at).toLocaleString() : ''"></p>
                            </div>
                        </div>
                    </article>
                </template>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function notificationBell(config) {
                return {
                    open: false,
                    userId: config.userId,
                    notifications: config.initialNotifications || [],

                    get unreadCount() {
                        return this.notifications.filter(notification => !notification.read_at).length;
                    },

                    init() {
                        if (!window.Echo) return;

                        window.Echo.private(`users.${this.userId}`)
                            .listen('.notification.created', (event) => {
                                this.notifications.unshift(event.notification);
                            });
                    },
                };
            }
        </script>
    @endpush
</div>
