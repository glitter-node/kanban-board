<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-ui-text-primary">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <x-layout.page as="div">
        <x-layout.section spacing="sm">
            <x-layout.container>
                <x-layout.stack space="6">
                    @if (session('error'))
                        <x-ui.state.error
                            title="Dashboard unavailable"
                            :message="session('error')"
                            retryLabel="Reload"
                            onclick="window.location.reload()"
                        />
                    @else
                        <x-ui.state.empty
                            title="Create your first board"
                            description="Start a project, organize tasks, and collaborate with your team in realtime."
                            actionLabel="Create board"
                            :actionHref="route('boards.create')"
                        />
                    @endif
                </x-layout.stack>
            </x-layout.container>
        </x-layout.section>
    </x-layout.page>
</x-app-layout>
