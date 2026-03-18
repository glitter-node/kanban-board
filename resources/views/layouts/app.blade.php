<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="Real-time collaborative kanban board with drag-and-drop, role-based access, and WebSocket sync.">

        <title>{{ config('app.name', 'Kanban Board') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles

        <style>[x-cloak] { display: none !important; }</style>
    </head>
    <body class="app-shell font-sans antialiased">
        <div class="min-h-screen">
            <a href="#main-content" class="sr-only absolute left-2 top-2 z-50 rounded-md bg-primary px-4 py-2 text-primary-foreground focus:not-sr-only">
                Skip to main content
            </a>

            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="border-b border-border bg-surface">
                    <div class="max-w-7xl mx-auto py-4 sm:py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main id="main-content" role="main">
                {{ $slot }}
            </main>

            <footer class="border-t border-border bg-surface py-6">
                <div class="mx-auto flex max-w-7xl justify-end px-4 sm:px-6 lg:px-8">
                    <a
                        href="https://glitter.kr"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Visit Glitter.kr"
                        class="text-zinc-500 transition-colors hover:text-zinc-300 focus:outline-none focus:ring-2 focus:ring-zinc-600"
                    >
                        Glitter.kr
                    </a>
                </div>
            </footer>
        </div>
        @livewireScripts
        @stack('scripts')
    </body>
</html>
