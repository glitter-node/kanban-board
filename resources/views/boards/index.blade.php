<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-foreground">
                My Boards
            </h2>
            <div class="flex items-center gap-3">
                <button @click="$store.theme.toggle()"
                        aria-label bg-surface text-secondary border border-border="Toggle dark mode"
                        class="btn-icon p-1.5 bg-primary text-white">
                    <svg x-show="$store.theme.current !== 'dark'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <svg x-show="$store.theme.current === 'dark'" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>
                <a href="{{ route('boards.create') }}"
                   class="btn-primary focus:ring-offset-canvas">
                    + New Board
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="ui-panel mb-6 px-4 py-3 text-success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($boards->isEmpty())
                <div class="ui-panel overflow-hidden sm:rounded-lg">
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-foreground">No boards yet</h3>
                        <p class="mt-2 text-sm text-secondary">Create a new board to start organizing your work.</p>
                        <div class="mt-6">
                            <a href="{{ route('boards.create') }}"
                               class="btn-primary">
                                + Create New Board
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($boards as $board)
                        <div class="ui-card-interactive overflow-visible sm:rounded-lg">
                            <a href="{{ route('boards.show', $board) }}" class="block p-6">
                                <div class="flex items-start justify-between">
                                    <h3 class="flex-1 truncate text-lg font-semibold text-surface-foreground">
                                        {{ $board->title }}
                                    </h3>
                                    <div class="flex items-center gap-1 ml-2" x-data="{ open: false }">
                                        <button @click.prevent="open = !open" aria-label bg-surface text-secondary border border-border="Open board actions" class="btn-icon p-1 bg-primary text-white">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/>
                                            </svg>
                                        </button>
                                        <div x-show="open" @click.away="open = false" x-cloak
                                             class="ui-panel absolute right-0 z-10 mt-20 w-36 p-1">
                                            <a href="{{ route('boards.edit', $board) }}"
                                               @click.stop
                                               class="block rounded-md px-4 py-2 text-sm text-secondary transition hover:bg-elevated hover:text-foreground">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('boards.destroy', $board) }}" @click.stop
                                                  onsubmit="return confirm('Are you sure you want to delete this board?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="block w-full rounded-md px-4 py-2 text-sm transition hover: bg-primary text-white">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                @if ($board->description)
                                    <p class="mt-2 line-clamp-2 text-sm text-surface-foreground text-secondary">{{ $board->description }}</p>
                                @endif

                                <div class="mt-4 flex items-center gap-4 text-xs text-surface-foreground text-secondary">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        {{ $board->columns_count }} columns
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                        {{ $board->cards_count }} cards
                                    </span>
                                    <span class="ml-auto">
                                        {{ $board->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
