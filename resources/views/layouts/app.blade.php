<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data x-bind:class="$store.darkMode.on ? 'dark' : ''">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="Real-time collaborative kanban board with drag-and-drop, role-based access, and WebSocket sync.">
        <meta name="theme-color" content="#0B1220">

        <title>{{ config('app.name', 'Kanban Board') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect x='10' y='15' width='22' height='70' rx='4' fill='%2322C55E'/><rect x='39' y='15' width='22' height='50' rx='4' fill='%2338BDF8'/><rect x='68' y='15' width='22' height='60' rx='4' fill='%23A78BFA'/></svg>">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('darkMode', {
                    on: localStorage.getItem('darkMode') === 'true',
                    toggle() {
                        this.on = !this.on;
                        localStorage.setItem('darkMode', this.on);
                    }
                });
            });
        </script>

        <style>[x-cloak] { display: none !important; }</style>
    </head>
    <body class="app-shell font-sans antialiased">
        <div class="min-h-screen transition-colors duration-200">
            <a href="#main-content" class="sr-only absolute left-2 top-2 z-50 rounded-md bg-ui-brand-primary px-4 py-2 text-canvas focus:not-sr-only">
                Skip to main content
            </a>

            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="border-b border-border bg-surface shadow-lg">
                    <div class="max-w-7xl mx-auto py-4 sm:py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main id="main-content" role="main">
                {{ $slot }}
            </main>
        </div>
        @livewireScripts
        @stack('scripts')
    </body>
</html>
