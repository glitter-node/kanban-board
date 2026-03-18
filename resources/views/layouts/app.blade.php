<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="Real-time collaborative kanban board with drag-and-drop, role-based access, and WebSocket sync.">

        <title>{{ config('app.name', 'Kanban Board') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles

        <style>[x-cloak] { display: none !important; }</style>
    </head>
    <body class="app-shell font-sans antialiased">
        <div class="min-h-screen bg-[var(--background)] text-[var(--text-primary)]">
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
        </div>
        @livewireScripts
        @stack('scripts')
    </body>
</html>
