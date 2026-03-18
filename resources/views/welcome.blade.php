<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta
            name="description"
            content="Organize work with simple realtime boards. Collaborative Kanban for teams and individuals built with Laravel."
        >

        <title>{{ config('app.name', 'Kanban Board') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="app-shell antialiased">
        <x-layout.page as="div" class="relative isolate overflow-hidden">
            <div class="app-hero-gradient absolute inset-0 -z-20"></div>
            <div class="app-hero-orbs absolute inset-0 -z-10"></div>
            <div class="absolute inset-x-0 top-0 -z-10 h-[38rem] bg-background"></div>

            <header>
                <x-layout.container class="flex items-center justify-between py-6">
                    <a href="/" class="flex items-center gap-3">
                        <span class="grid h-10 w-10 place-items-center rounded-2xl bg-surface ring-1 ring-border">
                            <span class="grid grid-cols-3 gap-1">
                                <span class="h-2 w-2 rounded-sm bg-primary"></span>
                                <span class="h-2 w-2 rounded-sm bg-primary"></span>
                                <span class="h-2 w-2 rounded-sm bg-primary"></span>
                                <span class="h-2 w-2 rounded-sm bg-muted-foreground"></span>
                                <span class="h-2 w-2 rounded-sm bg-surface-foreground"></span>
                                <span class="h-2 w-2 rounded-sm bg-foreground"></span>
                            </span>
                        </span>
                        <span class="text-sm font-semibold tracking-[0.22em] text-secondary uppercase">Board</span>
                    </a>

                    <nav class="hidden items-center gap-8 text-sm text-secondary md:flex">
                        <a href="#features" class="transition hover:text-foreground">Features</a>
                        <a href="#preview" class="transition hover:text-foreground">Preview</a>
                        <a href="#use-cases" class="transition hover:text-foreground">Use Cases</a>
                        <a href="{{ route('login') }}" class="transition hover:text-foreground">Sign in</a>
                    </nav>
                </x-layout.container>
            </header>

            <main>
                <x-layout.section class="pb-24 pt-10 lg:pb-32 lg:pt-12" spacing="none">
                    <x-layout.container>
                        <x-layout.grid :lg="null" class="gap-14 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)]">
                            <div class="max-w-2xl">
                        <div class="inline-flex items-center gap-2 rounded-full border border-primary bg-primary px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-primary-foreground text-secondary">
                            <span class="h-2 w-2 rounded-full bg-primary"></span>
                            Realtime collaborative workflow
                        </div>

                        <h1 class="mt-8 max-w-3xl text-5xl font-semibold tracking-tight text-foreground sm:text-6xl">
                            Organize work with simple realtime boards
                        </h1>

                        <p class="mt-6 max-w-2xl text-lg leading-8 text-secondary">
                            A collaborative Kanban system for teams and individuals to manage tasks and workflows in realtime.
                        </p>

                        <div class="mt-10 flex flex-col gap-3 sm:flex-row">
                            <a
                                href="{{ route('register') }}"
                                class="btn-primary rounded-2xl px-6 py-3 text-sm normal-case tracking-normal text-secondary"
                            >
                                Create a board
                            </a>
                            <a
                                href="#preview"
                                class="btn-secondary rounded-2xl px-6 py-3 text-sm normal-case tracking-normal text-secondary"
                            >
                                View demo
                            </a>
                        </div>

                        <div class="mt-12 grid gap-4 sm:grid-cols-3">
                            <div class="ui-panel p-4">
                                <p class="text-xs uppercase tracking-[0.18em] text-secondary">Boards</p>
                                <p class="mt-2 text-2xl font-semibold text-foreground">Personal + Team</p>
                            </div>
                            <div class="ui-panel p-4">
                                <p class="text-xs uppercase tracking-[0.18em] text-secondary">Sync</p>
                                <p class="mt-2 text-2xl font-semibold text-foreground">Realtime</p>
                            </div>
                            <div class="ui-panel p-4">
                                <p class="text-xs uppercase tracking-[0.18em] text-secondary">Flow</p>
                                <p class="mt-2 text-2xl font-semibold text-foreground">Visual</p>
                            </div>
                        </div>
                            </div>

                            <div class="relative">
                                <div class="absolute -left-8 top-12 h-28 w-28 rounded-full bg-primary"></div>
                                <div class="absolute -right-8 bottom-8 h-32 w-32 rounded-full bg-background"></div>

                                <div class="rounded-[2rem] border border-border bg-surface p-4">
                                    <div class="rounded-[1.7rem] border border-border bg-muted p-5">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-xs uppercase tracking-[0.18em] text-secondary">Product Launch</p>
                                                <h2 class="mt-2 text-xl font-semibold text-foreground">Board overview</h2>
                                            </div>
                                            <div class="flex -space-x-2">
                                                <span class="grid h-9 w-9 place-items-center rounded-full border border-border bg-elevated text-xs font-semibold text-secondary">AK</span>
                                                <span class="grid h-9 w-9 place-items-center rounded-full border border-border bg-elevated text-xs font-semibold text-secondary">JM</span>
                                                <span class="grid h-9 w-9 place-items-center rounded-full border border-border bg-elevated text-xs font-semibold text-secondary">LS</span>
                                            </div>
                                        </div>

                                        <div class="mt-6 grid gap-4 lg:grid-cols-4">
                                    <div class="rounded-3xl bg-surface p-4 ring-1 ring-border">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-semibold text-secondary">Backlog</p>
                                            <span class="ui-badge bg-surface text-secondary border border-border ui-badge bg-surface text-secondary border border-border-sm">6</span>
                                        </div>
                                        <div class="mt-4 space-y-3">
                                            <div class="rounded-2xl bg-elevated p-3 text-foreground ring-1 ring-border">
                                                <p class="text-sm font-semibold text-secondary">Outline release scope</p>
                                                <p class="mt-2 text-xs text-secondary">Brief · Spec</p>
                                            </div>
                                            <div class="rounded-2xl bg-elevated p-3 text-foreground ring-1 ring-border">
                                                <p class="text-sm font-semibold text-secondary">Collect beta notes</p>
                                                <p class="mt-2 text-xs text-secondary">Inbox</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="rounded-3xl bg-surface p-4 ring-1 ring-border">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-semibold text-secondary">In Progress</p>
                                            <span class="ui-badge bg-surface text-secondary border border-border ui-badge bg-surface text-secondary border border-border-sm ui-badge bg-surface text-secondary border border-border-info">3</span>
                                        </div>
                                        <div class="mt-4 space-y-3">
                                            <div class="rounded-2xl border border-primary bg-primary p-3 text-primary-foreground">
                                                <p class="text-sm font-semibold text-secondary">Build landing page</p>
                                                <div class="mt-3 flex items-center justify-between text-[11px] text-secondary">
                                                    <span>Due today</span>
                                                    <span>AK</span>
                                                </div>
                                            </div>
                                            <div class="rounded-2xl bg-elevated p-3 text-foreground ring-1 ring-border">
                                                <p class="text-sm font-semibold text-secondary">Finalize billing copy</p>
                                                <p class="mt-2 text-xs text-secondary">Review tomorrow</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="rounded-3xl bg-surface p-4 ring-1 ring-border">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-semibold text-secondary">Review</p>
                                            <span class="ui-badge bg-surface text-secondary border border-border ui-badge bg-surface text-secondary border border-border-sm ui-badge bg-surface text-secondary border border-border-warning">2</span>
                                        </div>
                                        <div class="mt-4 space-y-3">
                                            <div class="rounded-2xl bg-elevated p-3 text-foreground ring-1 ring-border">
                                                <p class="text-sm font-semibold text-secondary">Realtime event audit</p>
                                                <div class="mt-3 flex items-center justify-between text-[11px] text-secondary">
                                                    <span>12 comments</span>
                                                    <span>JM</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="rounded-3xl bg-surface p-4 ring-1 ring-border">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-semibold text-secondary">Done</p>
                                            <span class="ui-badge bg-surface text-secondary border border-border ui-badge bg-surface text-secondary border border-border-sm ui-badge bg-surface text-secondary border border-border-success">14</span>
                                        </div>
                                        <div class="mt-4 space-y-3">
                                            <div class="rounded-2xl bg-elevated p-3 text-foreground ring-1 ring-border">
                                                <p class="text-sm font-semibold text-secondary">API routes wired</p>
                                                <p class="mt-2 text-xs text-secondary">Delivered</p>
                                            </div>
                                            <div class="rounded-2xl bg-elevated p-3 text-foreground ring-1 ring-border">
                                                <p class="text-sm font-semibold text-secondary">Auth flow ready</p>
                                                <p class="mt-2 text-xs text-secondary">Completed</p>
                                            </div>
                                        </div>
                                    </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </x-layout.grid>
                    </x-layout.container>
                </x-layout.section>

                <x-layout.section class="border-y border-border bg-muted" spacing="lg">
                    <x-layout.container width="xl">
                        <div class="max-w-2xl">
                            <p class="ui-kicker text-secondary">Problem</p>
                            <h2 class="mt-4 text-3xl font-semibold text-foreground">Work becomes chaotic without structure</h2>
                        </div>

                        <div class="ui-panel mt-10 p-6">
                            <div class="grid gap-4 md:grid-cols-[repeat(5,minmax(0,1fr))] md:items-center">
                                <div class="rounded-3xl border border-border bg-surface p-5 text-center">
                                    <p class="text-sm font-semibold text-foreground text-secondary">Tasks</p>
                                </div>
                                <div class="text-center text-2xl text-secondary">→</div>
                                <div class="rounded-3xl border border-border bg-surface p-5 text-center">
                                    <p class="text-sm font-semibold text-foreground text-secondary">Chat</p>
                                </div>
                                <div class="text-center text-2xl text-secondary">→</div>
                                <div class="rounded-3xl border border-border bg-surface p-5 text-center">
                                    <p class="text-sm font-semibold text-foreground text-secondary">Email</p>
                                </div>
                            </div>
                            <div class="mt-4 grid gap-4 md:grid-cols-[repeat(3,minmax(0,1fr))] md:items-center">
                                <div class="rounded-3xl border border-border bg-surface p-5 text-center">
                                    <p class="text-sm font-semibold text-foreground text-secondary">Notes</p>
                                </div>
                                <div class="text-center text-2xl text-secondary">→</div>
                                <div class="rounded-3xl border border-error bg-error p-5 text-center">
                                    <p class="text-sm font-semibold text-error-foreground text-secondary">Missed deadlines</p>
                                </div>
                            </div>
                        </div>
                    </x-layout.container>
                </x-layout.section>

                <x-layout.section spacing="lg">
                    <x-layout.container width="xl">
                        <div class="max-w-2xl">
                            <p class="ui-kicker text-secondary">Solution</p>
                            <h2 class="mt-4 text-3xl font-semibold text-foreground">A clear workflow with Kanban</h2>
                            <p class="mt-4 text-base text-secondary">
                                Tasks move visually across workflow stages, so everyone sees what is next, what is blocked, and what is done.
                            </p>
                        </div>

                        <div class="ui-panel mt-10 p-6">
                            <div class="grid gap-4 lg:grid-cols-[repeat(7,minmax(0,1fr))] lg:items-center">
                                <div class="rounded-3xl bg-surface p-5 text-center ring-1 ring-border">
                                    <p class="text-sm font-semibold text-foreground text-secondary">Backlog</p>
                                </div>
                                <div class="text-center text-2xl text-secondary">→</div>
                                <div class="rounded-3xl bg-primary p-5 text-center ring-1 ring-primary">
                                    <p class="text-sm font-semibold text-primary-foreground text-secondary">In Progress</p>
                                </div>
                                <div class="text-center text-2xl text-secondary">→</div>
                                <div class="rounded-3xl bg-warning p-5 text-center ring-1 ring-warning">
                                    <p class="text-sm font-semibold text-warning-foreground text-secondary">Review</p>
                                </div>
                                <div class="text-center text-2xl text-secondary">→</div>
                                <div class="rounded-3xl bg-success p-5 text-center ring-1 ring-success">
                                    <p class="text-sm font-semibold text-success-foreground text-secondary">Done</p>
                                </div>
                            </div>
                        </div>
                    </x-layout.container>
                </x-layout.section>

                <x-layout.section id="features" spacing="lg">
                    <x-layout.container width="xl">
                        <div class="max-w-2xl">
                            <p class="ui-kicker text-secondary">Features</p>
                            <h2 class="mt-4 text-3xl font-semibold text-foreground">Built for focused workflow management</h2>
                        </div>

                        <x-layout.grid class="mt-10" md="2" lg="3" gap="5">
                            @php
                                $features = [
                                    ['icon' => '◌', 'title' => 'Realtime collaboration', 'text' => 'Board updates appear instantly across connected members.'],
                                    ['icon' => '↕', 'title' => 'Drag and drop workflow', 'text' => 'Move cards smoothly between workflow stages.'],
                                    ['icon' => '☰', 'title' => 'Task comments and activity', 'text' => 'Track discussion and history directly on work items.'],
                                    ['icon' => '◷', 'title' => 'Due dates and assignments', 'text' => 'Keep ownership and deadlines visible in the board.'],
                                    ['icon' => '◫', 'title' => 'Board permissions', 'text' => 'Separate owner, editor, and viewer access.'],
                                    ['icon' => '◉', 'title' => 'Notifications', 'text' => 'Receive assignment and mention alerts without leaving the flow.'],
                                ];
                            @endphp

                            @foreach ($features as $feature)
                                <article class="ui-panel p-6">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary text-lg font-semibold text-primary-foreground ring-1 ring-primary">
                                        {{ $feature['icon'] }}
                                    </div>
                                    <h3 class="mt-5 text-lg font-semibold text-foreground">{{ $feature['title'] }}</h3>
                                    <p class="mt-2 text-sm leading-6 text-secondary">{{ $feature['text'] }}</p>
                                </article>
                            @endforeach
                        </x-layout.grid>
                    </x-layout.container>
                </x-layout.section>

                <x-layout.section spacing="lg">
                    <x-layout.container width="xl">
                        <div class="max-w-2xl">
                            <p class="ui-kicker text-secondary">How It Works</p>
                            <h2 class="mt-4 text-3xl font-semibold text-foreground">Simple board setup, visible execution</h2>
                        </div>

                        <div class="ui-panel mt-10 p-6">
                            <div class="grid gap-4 md:grid-cols-5 md:items-center">
                                @php
                                    $steps = ['Create Board', 'Add Columns', 'Create Cards', 'Move Tasks', 'Team Collaboration'];
                                @endphp
                                @foreach ($steps as $index => $step)
                                    <div class="rounded-3xl bg-surface p-5 text-center ring-1 ring-border">
                                        <p class="text-sm font-semibold text-foreground text-secondary">{{ $step }}</p>
                                    </div>
                                    @if ($index < count($steps) - 1)
                                        <div class="text-center text-2xl text-secondary">↓</div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </x-layout.container>
                </x-layout.section>

                <x-layout.section id="preview" spacing="lg">
                    <x-layout.container width="xl">
                        <div class="max-w-2xl">
                            <p class="ui-kicker text-secondary">Product Preview</p>
                            <h2 class="mt-4 text-3xl font-semibold text-foreground">A board interface built around movement and visibility</h2>
                        </div>

                        <div class="ui-panel mt-10 rounded-[2.2rem] p-5">
                            <div class="rounded-[1.8rem] bg-muted p-5 ring-1 ring-border">
                                <div class="grid gap-6 lg:grid-cols-[minmax(0,1.2fr)_minmax(18rem,0.8fr)]">
                                    <div class="overflow-hidden rounded-[1.6rem] border border-border bg-surface">
                                        <div class="flex items-center justify-between border-b border-border px-5 py-4">
                                            <div>
                                                <p class="text-xs uppercase tracking-[0.18em] text-secondary">Board UI</p>
                                                <h3 class="mt-1 text-lg font-semibold text-foreground">Sprint planning</h3>
                                            </div>
                                            <div class="flex -space-x-2">
                                                <span class="h-8 w-8 rounded-full border border-border bg-elevated"></span>
                                                <span class="h-8 w-8 rounded-full border border-border bg-elevated"></span>
                                                <span class="h-8 w-8 rounded-full border border-border bg-elevated"></span>
                                            </div>
                                        </div>

                                        <div class="grid gap-4 p-4 md:grid-cols-4">
                                            @foreach (['Columns', 'Cards', 'Comments', 'Due dates'] as $label bg-surface text-secondary border border-border)
                                                <div class="rounded-3xl bg-muted p-4 ring-1 ring-border">
                                                    <p class="text-xs uppercase tracking-[0.16em] text-secondary">{{ $label bg-surface text-secondary border border-border }}</p>
                                                    <div class="mt-4 space-y-3">
                                                        <div class="rounded-2xl bg-elevated p-3 text-foreground ring-1 ring-border">
                                                            <div class="h-2.5 w-20 rounded-full bg-surface-foreground"></div>
                                                            <div class="mt-3 h-2 w-14 rounded-full bg-muted-foreground"></div>
                                                        </div>
                                                        <div class="rounded-2xl bg-elevated p-3 text-foreground ring-1 ring-border">
                                                            <div class="h-2.5 w-24 rounded-full bg-surface-foreground"></div>
                                                            <div class="mt-3 h-2 w-10 rounded-full bg-muted-foreground"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        <div class="ui-panel rounded-[1.6rem] p-5">
                                            <p class="text-xs uppercase tracking-[0.18em] text-secondary">Visible details</p>
                                            <div class="mt-5 space-y-4">
                                                <div class="flex items-center justify-between rounded-2xl bg-elevated px-4 py-3">
                                                    <span class="text-sm text-secondary">Columns</span>
                                                    <span class="text-sm font-semibold text-foreground text-secondary">Workflow stages</span>
                                                </div>
                                                <div class="flex items-center justify-between rounded-2xl bg-elevated px-4 py-3">
                                                    <span class="text-sm text-secondary">Cards</span>
                                                    <span class="text-sm font-semibold text-foreground text-secondary">Work items</span>
                                                </div>
                                                <div class="flex items-center justify-between rounded-2xl bg-elevated px-4 py-3">
                                                    <span class="text-sm text-secondary">User avatars</span>
                                                    <span class="text-sm font-semibold text-foreground text-secondary">Owners</span>
                                                </div>
                                                <div class="flex items-center justify-between rounded-2xl bg-elevated px-4 py-3">
                                                    <span class="text-sm text-secondary">Comments</span>
                                                    <span class="text-sm font-semibold text-foreground text-secondary">Context</span>
                                                </div>
                                                <div class="flex items-center justify-between rounded-2xl bg-elevated px-4 py-3">
                                                    <span class="text-sm text-secondary">Due dates</span>
                                                    <span class="text-sm font-semibold text-foreground text-secondary">Timing</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="rounded-[1.6rem] border border-primary bg-primary p-5">
                                            <p class="text-sm font-semibold text-primary-foreground text-secondary">Move work, keep context, stay aligned.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-layout.container>
                </x-layout.section>

                <x-layout.section id="use-cases" spacing="lg">
                    <x-layout.container width="xl">
                        <div class="max-w-2xl">
                            <p class="ui-kicker text-secondary">Use Cases</p>
                            <h2 class="mt-4 text-3xl font-semibold text-foreground">One board model, multiple ways to work</h2>
                        </div>

                        <x-layout.grid class="mt-10" md="2" lg="4" gap="5">
                            @php
                                $useCases = [
                                    ['icon' => '▣', 'title' => 'Team project management'],
                                    ['icon' => '◎', 'title' => 'Personal task boards'],
                                    ['icon' => '◍', 'title' => 'Remote collaboration'],
                                    ['icon' => '◫', 'title' => 'Product development workflows'],
                                ];
                            @endphp

                            @foreach ($useCases as $useCase)
                                <article class="ui-panel p-6">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-surface text-lg font-semibold text-primary-foreground ring-1 ring-border">
                                        {{ $useCase['icon'] }}
                                    </div>
                                    <h3 class="mt-5 text-lg font-semibold text-foreground">{{ $useCase['title'] }}</h3>
                                </article>
                            @endforeach
                        </x-layout.grid>
                    </x-layout.container>
                </x-layout.section>

                <x-layout.section class="pb-24 pt-8" spacing="none">
                    <x-layout.container width="lg">
                        <div class="app-glass-panel rounded-[2.4rem] p-8 sm:p-10">
                            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
                                <div>
                                    <p class="ui-kicker text-secondary">Final CTA</p>
                                    <h2 class="mt-4 text-3xl font-semibold text-foreground">Start organizing your work</h2>
                                </div>

                                <div class="flex flex-col gap-3 sm:flex-row">
                                    <a
                                        href="{{ route('register') }}"
                                        class="btn-primary rounded-2xl px-6 py-3 text-sm normal-case tracking-normal text-secondary"
                                    >
                                        Create board
                                    </a>
                                    <a
                                        href="{{ route('login') }}"
                                        class="btn-secondary rounded-2xl px-6 py-3 text-sm normal-case tracking-normal text-secondary"
                                    >
                                        Sign in
                                    </a>
                                </div>
                            </div>
                        </div>
                    </x-layout.container>
                </x-layout.section>
            </main>

            <x-layout.divider class="bg-border" />
            <footer class="py-10">
                <x-layout.container class="flex flex-col gap-5 text-sm text-secondary md:flex-row md:items-center md:justify-between">
                    <p>Board for collaborative Kanban workflow.</p>
                    <nav class="flex flex-wrap gap-x-6 gap-y-3">
                        <a href="#features" class="transition hover:text-foreground">Features</a>
                        <a href="#preview" class="transition hover:text-foreground">Documentation</a>
                        <a href="{{ route('login') }}" class="transition hover:text-foreground">Sign in</a>
                        <a href="{{ route('register') }}" class="transition hover:text-foreground">Register</a>
                        <a href="#" class="transition hover:text-foreground">Privacy</a>
                        <a href="#" class="transition hover:text-foreground">Terms</a>
                    </nav>
                </x-layout.container>
            </footer>
        </x-layout.page>
    </body>
</html>
