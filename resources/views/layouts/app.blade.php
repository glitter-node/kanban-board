<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="app-experiments" content='@json($frontendExperiments ?? [])'>
        <meta name="description" content="Real-time collaborative kanban board with drag-and-drop, role-based access, and WebSocket sync.">

        <title>{{ config('app.name', 'Kanban Board') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles

        <style>[x-cloak] { display: none !important; }</style>
    </head>
    <body class="app-shell font-sans antialiased">
        <div x-data="{ feedbackOpen: false, sentiment: 'positive', comment: '' }" class="min-h-screen bg-[var(--background)] text-[var(--text-primary)]">
            <a href="#main-content" class="sr-only absolute left-2 top-2 z-50 rounded-md bg-primary px-4 py-2 text-primary-foreground focus:not-sr-only">
                Skip to main content
            </a>

            @include('layouts.navigation')

            @isset($header)
                <header class="px-4 py-6 sm:px-6 lg:px-8">
                    <x-ui.surface class="mx-auto max-w-7xl px-4 py-4 sm:px-6 sm:py-6">
                        {{ $header }}
                    </x-ui.surface>
                </header>
            @endisset

            <main id="main-content" role="main">
                {{ $slot }}
            </main>

            <footer class="px-4 py-6 sm:px-6 lg:px-8">
                <x-ui.surface class="mx-auto flex max-w-7xl justify-end px-4 py-4 sm:px-6">
                    <a
                        href="https://glitter.kr"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Visit Glitter.kr"
                        class="text-secondary transition-colors hover:text-foreground focus:outline-none focus:ring-2 focus:ring-border"
                    >
                        Glitter.kr
                    </a>
                </x-ui.surface>
            </footer>

            <div class="fixed bottom-6 right-6 z-40 flex flex-col items-end gap-3">
                <x-ui.panel x-show="feedbackOpen" x-cloak elevated="true" class="w-full max-w-sm p-0">
                    <div class="space-y-4 p-5">
                        <div>
                            <h2 class="text-base font-semibold">Board feedback</h2>
                            <p class="mt-1 text-sm text-secondary">Rate the current experience and leave a short note.</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <x-ui.button type="button" variant="secondary" x-bind:class="sentiment === 'positive' ? 'ui-glow' : ''" @click="sentiment = 'positive'">
                                Positive
                            </x-ui.button>
                            <x-ui.button type="button" variant="secondary" x-bind:class="sentiment === 'negative' ? 'ui-glow' : ''" @click="sentiment = 'negative'">
                                Negative
                            </x-ui.button>
                        </div>

                        <x-ui.input as="textarea" rows="4" x-model="comment" placeholder="What is slowing you down?" />

                        <div class="flex items-center justify-end gap-3">
                            <x-ui.button type="button" variant="secondary" @click="feedbackOpen = false">
                                Close
                            </x-ui.button>
                            <x-ui.button
                                type="button"
                                @click="
                                    window.submitUxFeedback({
                                        sentiment,
                                        comment,
                                        context: window.location.pathname,
                                    });
                                    feedbackOpen = false;
                                    comment = '';
                                "
                            >
                                Send feedback
                            </x-ui.button>
                        </div>
                    </div>
                </x-ui.panel>

                <x-ui.button
                    type="button"
                    @click="
                        feedbackOpen = !feedbackOpen;
                        if (feedbackOpen) {
                            window.trackEvent('feedback_opened', { context: window.location.pathname });
                        }
                    "
                >
                    Feedback
                </x-ui.button>
            </div>
        </div>
        @livewireScripts
        @stack('scripts')
    </body>
</html>
