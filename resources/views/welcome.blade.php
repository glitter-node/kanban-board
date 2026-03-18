<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-experiments" content='@json($frontendExperiments ?? [])'>
    <meta name="description" content="Organize work with simple realtime boards. Collaborative Kanban for teams and individuals built with Laravel.">
    <title>{{ config('app.name', 'Kanban Board') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-shell antialiased">
<div class="min-h-screen bg-[var(--background)] text-[var(--text-primary)]">
    <x-layout.page as="div" class="relative isolate overflow-hidden">
        <div class="app-hero-gradient absolute inset-0 -z-20"></div>
        <div class="app-hero-orbs absolute inset-0 -z-10"></div>
        <div class="absolute inset-x-0 top-0 -z-10 h-[38rem] bg-[var(--background)]"></div>

        <header>
            <x-layout.container class="flex items-center justify-between py-6">
                <a href="/" class="flex items-center gap-3">
                    <x-ui.surface as="span" variant="elevated" class="grid h-10 w-10 place-items-center rounded-2xl p-0">
                        <span class="grid grid-cols-3 gap-1">
                            <span class="h-2 w-2 rounded-sm bg-[var(--primary)]"></span>
                            <span class="h-2 w-2 rounded-sm bg-[var(--primary)]"></span>
                            <span class="h-2 w-2 rounded-sm bg-[var(--primary)]"></span>
                            <span class="h-2 w-2 rounded-sm bg-[var(--text-secondary)]"></span>
                            <span class="h-2 w-2 rounded-sm bg-[var(--text-primary)]"></span>
                            <span class="h-2 w-2 rounded-sm bg-[var(--text-primary)]"></span>
                        </span>
                    </x-ui.surface>
                    <span class="text-sm font-semibold uppercase tracking-[0.22em] text-[var(--text-secondary)]">Board</span>
                </a>

                <nav class="hidden items-center gap-8 text-sm text-[var(--text-secondary)] md:flex">
                    <a href="#features" class="transition-colors hover:text-[var(--text-primary)]">Features</a>
                    <a href="#preview" class="transition-colors hover:text-[var(--text-primary)]">Preview</a>
                    <a href="#use-cases" class="transition-colors hover:text-[var(--text-primary)]">Use Cases</a>
                    <a href="{{ route('login') }}" class="transition-colors hover:text-[var(--text-primary)]">Sign in</a>
                </nav>
            </x-layout.container>
        </header>

        <main>
            <x-ui.section class="pb-24 pt-10 lg:pb-32 lg:pt-12" width="7xl">
                    <x-layout.grid :lg="null" class="gap-14 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)]">
                        <div class="max-w-2xl">
                            <div class="inline-flex items-center gap-2 rounded-full border border-[var(--border)] bg-[var(--surface-elevated)] px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--text-primary)]">
                                <span class="h-2 w-2 rounded-full bg-[var(--primary)]"></span>
                                Realtime collaborative workflow
                            </div>

                            <h1 class="mt-8 max-w-3xl text-5xl font-semibold tracking-tight text-[var(--text-primary)] sm:text-6xl">
                                Organize work with simple realtime boards
                            </h1>

                            <p class="mt-6 max-w-2xl text-lg leading-8 text-[var(--text-secondary)]">
                                A collaborative Kanban system for teams and individuals to manage tasks and workflows in realtime.
                            </p>

                            <div class="mt-10 flex flex-col gap-3 sm:flex-row">
                                @experiment('landing_primary_cta')
                                    @variant('A')
                                        <x-ui.button as="a" href="{{ route('register') }}" variant="primary" size="lg" class="rounded-2xl normal-case tracking-normal">
                                            Create a board
                                        </x-ui.button>
                                    @endvariant
                                    @variant('B')
                                        <x-ui.button as="a" href="{{ route('register') }}" variant="primary" size="lg" class="rounded-2xl normal-case tracking-normal">
                                            Start your board
                                        </x-ui.button>
                                    @endvariant
                                @endexperiment
                                <x-ui.button as="a" href="#preview" variant="secondary" size="lg" class="rounded-2xl normal-case tracking-normal">
                                    View demo
                                </x-ui.button>
                            </div>

                            <div class="mt-12 grid gap-4 sm:grid-cols-3">
                                <x-ui.card class="p-4">
                                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--text-secondary)]">Boards</p>
                                    <p class="mt-2 text-2xl font-semibold text-[var(--text-primary)]">Personal + Team</p>
                                </x-ui.card>
                                <x-ui.card class="p-4">
                                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--text-secondary)]">Sync</p>
                                    <p class="mt-2 text-2xl font-semibold text-[var(--text-primary)]">Realtime</p>
                                </x-ui.card>
                                <x-ui.card class="p-4">
                                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--text-secondary)]">Flow</p>
                                    <p class="mt-2 text-2xl font-semibold text-[var(--text-primary)]">Visual</p>
                                </x-ui.card>
                            </div>
                        </div>

                        <div class="relative">
                            <div class="absolute -left-8 top-12 h-28 w-28 rounded-full bg-[var(--primary)]"></div>
                            <div class="absolute -right-8 bottom-8 h-32 w-32 rounded-full bg-[var(--surface)]"></div>

                            <x-ui.surface variant="elevated" class="rounded-[2rem] p-4">
                                <x-ui.surface class="rounded-[1.7rem] p-5">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.18em] text-[var(--text-secondary)]">Product Launch</p>
                                            <h2 class="mt-2 text-xl font-semibold text-[var(--text-primary)]">Board overview</h2>
                                        </div>
                                        <div class="flex -space-x-2">
                                            <x-ui.surface as="span" variant="elevated" class="grid h-9 w-9 place-items-center rounded-full p-0 text-xs font-semibold text-[var(--text-secondary)]">AK</x-ui.surface>
                                            <x-ui.surface as="span" variant="elevated" class="grid h-9 w-9 place-items-center rounded-full p-0 text-xs font-semibold text-[var(--text-secondary)]">JM</x-ui.surface>
                                            <x-ui.surface as="span" variant="elevated" class="grid h-9 w-9 place-items-center rounded-full p-0 text-xs font-semibold text-[var(--text-secondary)]">LS</x-ui.surface>
                                        </div>
                                    </div>

                                    <div class="mt-6 grid gap-4 lg:grid-cols-4">
                                        <x-ui.card class="rounded-3xl p-4">
                                            <div class="flex items-center justify-between">
                                                <p class="text-sm font-semibold text-[var(--text-primary)]">Backlog</p>
                                                <span class="ui-badge ui-badge-sm">6</span>
                                            </div>
                                            <div class="mt-4 space-y-3">
                                                <x-ui.surface variant="elevated" class="rounded-2xl p-3 text-[var(--text-primary)]">
                                                    <p class="text-sm font-semibold">Outline release scope</p>
                                                    <p class="mt-2 text-xs text-[var(--text-secondary)]">Brief · Spec</p>
                                                </x-ui.surface>
                                                <x-ui.surface variant="elevated" class="rounded-2xl p-3 text-[var(--text-primary)]">
                                                    <p class="text-sm font-semibold">Collect beta notes</p>
                                                    <p class="mt-2 text-xs text-[var(--text-secondary)]">Inbox</p>
                                                </x-ui.surface>
                                            </div>
                                        </x-ui.card>

                                        <x-ui.card class="rounded-3xl p-4">
                                            <div class="flex items-center justify-between">
                                                <p class="text-sm font-semibold text-[var(--text-primary)]">In Progress</p>
                                                <span class="ui-badge ui-badge-sm ui-badge-info">3</span>
                                            </div>
                                            <div class="mt-4 space-y-3">
                                                <div class="ui-glow rounded-2xl border border-[var(--primary)] bg-[var(--primary)] p-3 text-[var(--primary-foreground)]">
                                                    <p class="text-sm font-semibold">Build landing page</p>
                                                    <div class="mt-3 flex items-center justify-between text-[11px] text-[var(--primary-foreground)]">
                                                        <span>Due today</span>
                                                        <span>AK</span>
                                                    </div>
                                                </div>
                                                <x-ui.surface variant="elevated" class="rounded-2xl p-3 text-[var(--text-primary)]">
                                                    <p class="text-sm font-semibold">Finalize billing copy</p>
                                                    <p class="mt-2 text-xs text-[var(--text-secondary)]">Review tomorrow</p>
                                                </x-ui.surface>
                                            </div>
                                        </x-ui.card>

                                        <x-ui.card class="rounded-3xl p-4">
                                            <div class="flex items-center justify-between">
                                                <p class="text-sm font-semibold text-[var(--text-primary)]">Review</p>
                                                <span class="ui-badge ui-badge-sm ui-badge-warning">2</span>
                                            </div>
                                            <div class="mt-4 space-y-3">
                                                <x-ui.surface variant="elevated" class="rounded-2xl p-3 text-[var(--text-primary)]">
                                                    <p class="text-sm font-semibold">Realtime event audit</p>
                                                    <div class="mt-3 flex items-center justify-between text-[11px] text-[var(--text-secondary)]">
                                                        <span>12 comments</span>
                                                        <span>JM</span>
                                                    </div>
                                                </x-ui.surface>
                                            </div>
                                        </x-ui.card>

                                        <x-ui.card class="rounded-3xl p-4">
                                            <div class="flex items-center justify-between">
                                                <p class="text-sm font-semibold text-[var(--text-primary)]">Done</p>
                                                <span class="ui-badge ui-badge-sm ui-badge-success">14</span>
                                            </div>
                                            <div class="mt-4 space-y-3">
                                                <x-ui.surface variant="elevated" class="rounded-2xl p-3 text-[var(--text-primary)]">
                                                    <p class="text-sm font-semibold">API routes wired</p>
                                                    <p class="mt-2 text-xs text-[var(--text-secondary)]">Delivered</p>
                                                </x-ui.surface>
                                                <x-ui.surface variant="elevated" class="rounded-2xl p-3 text-[var(--text-primary)]">
                                                    <p class="text-sm font-semibold">Auth flow ready</p>
                                                    <p class="mt-2 text-xs text-[var(--text-secondary)]">Completed</p>
                                                </x-ui.surface>
                                            </div>
                                        </x-ui.card>
                                    </div>
                                </x-ui.surface>
                            </x-ui.surface>
                        </div>
                    </x-layout.grid>
            </x-ui.section>

            <x-ui.section class="border-y border-[var(--border)] bg-[var(--surface)]" width="xl">
                    <div class="max-w-2xl">
                        <p class="ui-kicker text-[var(--primary)]">Problem</p>
                        <h2 class="mt-4 text-3xl font-semibold text-[var(--text-primary)]">Work becomes chaotic without structure</h2>
                    </div>

                    <x-ui.surface variant="elevated" class="mt-10 p-6">
                        <div class="grid gap-4 md:grid-cols-[repeat(5,minmax(0,1fr))] md:items-center">
                            <x-ui.card class="p-5 text-center">
                                <p class="text-sm font-semibold text-[var(--text-primary)]">Tasks</p>
                            </x-ui.card>
                            <div class="text-center text-2xl text-[var(--text-secondary)]">→</div>
                            <x-ui.card class="p-5 text-center">
                                <p class="text-sm font-semibold text-[var(--text-primary)]">Chat</p>
                            </x-ui.card>
                            <div class="text-center text-2xl text-[var(--text-secondary)]">→</div>
                            <x-ui.card class="p-5 text-center">
                                <p class="text-sm font-semibold text-[var(--text-primary)]">Email</p>
                            </x-ui.card>
                        </div>

                        <div class="mt-4 grid gap-4 md:grid-cols-[repeat(3,minmax(0,1fr))] md:items-center">
                            <x-ui.card class="p-5 text-center">
                                <p class="text-sm font-semibold text-[var(--text-primary)]">Notes</p>
                            </x-ui.card>
                            <div class="text-center text-2xl text-[var(--text-secondary)]">→</div>
                            <div class="rounded-3xl border border-[var(--danger)] bg-[var(--danger)] p-5 text-center text-[var(--danger-foreground)] shadow-[var(--shadow-md)]">
                                <p class="text-sm font-semibold">Missed deadlines</p>
                            </div>
                        </div>
                    </x-ui.surface>
            </x-ui.section>

            <x-ui.section width="xl">
                    <div class="max-w-2xl">
                        <p class="ui-kicker text-[var(--primary)]">Solution</p>
                        <h2 class="mt-4 text-3xl font-semibold text-[var(--text-primary)]">A clear workflow with Kanban</h2>
                        <p class="mt-4 text-base text-[var(--text-secondary)]">
                            Tasks move visually across workflow stages, so everyone sees what is next, what is blocked, and what is done.
                        </p>
                    </div>

                    <x-ui.surface variant="elevated" class="mt-10 rounded-xl p-6">
                        <div class="grid gap-4 lg:grid-cols-[repeat(7,minmax(0,1fr))] lg:items-center">
                            <x-ui.surface class="rounded-lg px-4 py-2 text-center text-[var(--text-primary)]">
                                Backlog
                            </x-ui.surface>
                            <div class="text-center text-2xl text-[var(--text-secondary)]">→</div>
                            <div class="ui-glow rounded-lg bg-[var(--primary)] px-4 py-2 text-center text-[var(--primary-foreground)]">
                                In Progress
                            </div>
                            <div class="text-center text-2xl text-[var(--text-secondary)]">→</div>
                            <x-ui.surface class="rounded-lg px-4 py-2 text-center text-[var(--text-primary)]">
                                Review
                            </x-ui.surface>
                            <div class="text-center text-2xl text-[var(--text-secondary)]">→</div>
                            <x-ui.surface class="rounded-lg px-4 py-2 text-center text-[var(--text-primary)]">
                                Done
                            </x-ui.surface>
                        </div>
                    </x-ui.surface>
            </x-ui.section>

            <x-ui.section id="features" width="xl">
                    <div class="max-w-2xl">
                        <p class="ui-kicker text-[var(--primary)]">Features</p>
                        <h2 class="mt-4 text-3xl font-semibold text-[var(--text-primary)]">Built for focused workflow management</h2>
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
                            <x-ui.card as="article">
                                <div class="mb-4 text-[var(--primary)]">
                                    <div class="ui-glow flex h-12 w-12 items-center justify-center rounded-2xl border border-[var(--border)] bg-[var(--surface-elevated)] text-lg font-semibold">
                                        {{ $feature['icon'] }}
                                    </div>
                                </div>
                                <h3 class="mt-5 text-lg font-semibold text-[var(--text-primary)]">{{ $feature['title'] }}</h3>
                                <p class="mt-2 text-sm leading-6 text-[var(--text-secondary)]">{{ $feature['text'] }}</p>
                            </x-ui.card>
                        @endforeach
                    </x-layout.grid>
            </x-ui.section>

            <x-ui.section width="xl">
                    <div class="max-w-2xl">
                        <p class="ui-kicker text-[var(--primary)]">How It Works</p>
                        <h2 class="mt-4 text-3xl font-semibold text-[var(--text-primary)]">Simple board setup, visible execution</h2>
                    </div>

                    <x-ui.surface variant="elevated" class="mt-10 p-6">
                        <div class="grid gap-4 md:grid-cols-5 md:items-center">
                            @php
                                $steps = ['Create Board', 'Add Columns', 'Create Cards', 'Move Tasks', 'Team Collaboration'];
                            @endphp

                            @foreach ($steps as $index => $step)
                                <x-ui.card class="p-5 text-center">
                                    <p class="text-sm font-semibold text-[var(--text-primary)]">{{ $step }}</p>
                                </x-ui.card>

                                @if ($index < count($steps) - 1)
                                    <div class="text-center text-2xl text-[var(--text-secondary)]">↓</div>
                                @endif
                            @endforeach
                        </div>
                    </x-ui.surface>
            </x-ui.section>

            <x-ui.section id="preview" width="xl">
                    <div class="max-w-2xl">
                        <p class="ui-kicker text-[var(--primary)]">Product Preview</p>
                        <h2 class="mt-4 text-3xl font-semibold text-[var(--text-primary)]">A board interface built around movement and visibility</h2>
                    </div>

                    <x-ui.surface variant="elevated" class="mt-10 rounded-2xl p-5">
                        <x-ui.surface class="rounded-[1.8rem] p-5">
                            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.2fr)_minmax(18rem,0.8fr)]">
                                <x-ui.surface class="overflow-hidden rounded-[1.6rem]">
                                    <div class="flex items-center justify-between border-b border-[var(--border)] px-5 py-4">
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.18em] text-[var(--text-secondary)]">Board UI</p>
                                            <h3 class="mt-1 text-lg font-semibold text-[var(--text-primary)]">Sprint planning</h3>
                                        </div>
                                        <div class="flex -space-x-2">
                                            <x-ui.surface as="span" variant="elevated" class="h-8 w-8 rounded-full p-0"></x-ui.surface>
                                            <x-ui.surface as="span" variant="elevated" class="h-8 w-8 rounded-full p-0"></x-ui.surface>
                                            <x-ui.surface as="span" variant="elevated" class="h-8 w-8 rounded-full p-0"></x-ui.surface>
                                        </div>
                                    </div>

                                    <div class="grid gap-4 p-4 md:grid-cols-4">
                                        @foreach (['Columns', 'Cards', 'Comments', 'Due dates'] as $label)
                                            <x-ui.card class="rounded-3xl p-4">
                                                <p class="text-xs uppercase tracking-[0.16em] text-[var(--text-secondary)]">{{ $label }}</p>
                                                <div class="mt-4 space-y-3">
                                                    <x-ui.surface variant="elevated" class="rounded-2xl p-3 text-[var(--text-primary)]">
                                                        <div class="h-2.5 w-20 rounded-full bg-[var(--text-primary)]"></div>
                                                        <div class="mt-3 h-2 w-14 rounded-full bg-[var(--text-secondary)]"></div>
                                                    </x-ui.surface>
                                                    <x-ui.surface variant="elevated" class="rounded-2xl p-3 text-[var(--text-primary)]">
                                                        <div class="h-2.5 w-24 rounded-full bg-[var(--text-primary)]"></div>
                                                        <div class="mt-3 h-2 w-10 rounded-full bg-[var(--text-secondary)]"></div>
                                                    </x-ui.surface>
                                                </div>
                                            </x-ui.card>
                                        @endforeach
                                    </div>
                                </x-ui.surface>

                                <div class="space-y-4">
                                    <x-ui.card class="rounded-[1.6rem] p-5">
                                        <p class="text-xs uppercase tracking-[0.18em] text-[var(--text-secondary)]">Visible details</p>
                                        <div class="mt-5 space-y-4">
                                            <x-ui.surface variant="elevated" class="flex items-center justify-between rounded-2xl px-4 py-3">
                                                <span class="text-sm text-[var(--text-secondary)]">Columns</span>
                                                <span class="text-sm font-semibold text-[var(--text-primary)]">Workflow stages</span>
                                            </x-ui.surface>
                                            <x-ui.surface variant="elevated" class="flex items-center justify-between rounded-2xl px-4 py-3">
                                                <span class="text-sm text-[var(--text-secondary)]">Cards</span>
                                                <span class="text-sm font-semibold text-[var(--text-primary)]">Work items</span>
                                            </x-ui.surface>
                                            <x-ui.surface variant="elevated" class="flex items-center justify-between rounded-2xl px-4 py-3">
                                                <span class="text-sm text-[var(--text-secondary)]">User avatars</span>
                                                <span class="text-sm font-semibold text-[var(--text-primary)]">Owners</span>
                                            </x-ui.surface>
                                            <x-ui.surface variant="elevated" class="flex items-center justify-between rounded-2xl px-4 py-3">
                                                <span class="text-sm text-[var(--text-secondary)]">Comments</span>
                                                <span class="text-sm font-semibold text-[var(--text-primary)]">Context</span>
                                            </x-ui.surface>
                                            <x-ui.surface variant="elevated" class="flex items-center justify-between rounded-2xl px-4 py-3">
                                                <span class="text-sm text-[var(--text-secondary)]">Due dates</span>
                                                <span class="text-sm font-semibold text-[var(--text-primary)]">Timing</span>
                                            </x-ui.surface>
                                        </div>
                                    </x-ui.card>

                                    <div class="ui-glow rounded-[1.6rem] border border-[var(--primary)] bg-[var(--primary)] p-5 text-[var(--primary-foreground)]">
                                        <p class="text-sm font-semibold">Move work, keep context, stay aligned.</p>
                                    </div>
                                </div>
                            </div>
                        </x-ui.surface>
                    </x-ui.surface>
            </x-ui.section>

            <x-ui.section id="use-cases" width="xl">
                    <div class="max-w-2xl">
                        <p class="ui-kicker text-[var(--primary)]">Use Cases</p>
                        <h2 class="mt-4 text-3xl font-semibold text-[var(--text-primary)]">One board model, multiple ways to work</h2>
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
                            <x-ui.card as="article">
                                <x-ui.surface class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl p-0 text-lg font-semibold text-[var(--primary)]" variant="elevated">
                                    {{ $useCase['icon'] }}
                                </x-ui.surface>
                                <h3 class="mt-5 text-lg font-semibold text-[var(--text-primary)]">{{ $useCase['title'] }}</h3>
                            </x-ui.card>
                        @endforeach
                    </x-layout.grid>
            </x-ui.section>

            <x-ui.section class="pb-24 pt-8" width="lg">
                    <x-ui.card class="flex items-center justify-between rounded-[2.4rem] p-8 sm:p-10">
                        <div>
                            <p class="ui-kicker text-[var(--primary)]">Final CTA</p>
                            <h2 class="mt-4 text-3xl font-semibold text-[var(--text-primary)]">Start organizing your work</h2>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row">
                            <x-ui.button as="a" href="{{ route('register') }}" variant="primary" size="lg" class="rounded-2xl normal-case tracking-normal">
                                Create board
                            </x-ui.button>
                            <x-ui.button as="a" href="{{ route('login') }}" variant="secondary" size="lg" class="rounded-2xl normal-case tracking-normal">
                                Sign in
                            </x-ui.button>
                        </div>
                    </x-ui.card>
            </x-ui.section>
        </main>

        <x-layout.divider class="bg-border" />

        <footer class="py-10 text-[var(--text-secondary)]">
            <x-layout.container class="flex flex-col items-center justify-between gap-5 text-sm text-[var(--text-secondary)] md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <span>Board for collaborative Kanban workflow.</span>
                    <a href="https://glitter.kr" target="_blank" rel="noopener noreferrer" aria-label="Visit Glitter.kr" class="transition-colors hover:text-[var(--text-primary)]">
                        Glitter.kr
                    </a>
                </div>

                <div class="flex gap-6 flex-wrap">
                    <a href="#features" class="transition-colors hover:text-[var(--text-primary)]">Features</a>
                    <a href="#preview" class="transition-colors hover:text-[var(--text-primary)]">Documentation</a>
                    <a href="{{ route('login') }}" class="transition-colors hover:text-[var(--text-primary)]">Sign in</a>
                    <a href="{{ route('register') }}" class="transition-colors hover:text-[var(--text-primary)]">Register</a>
                    <a href="#" class="transition-colors hover:text-[var(--text-primary)]">Privacy</a>
                    <a href="#" class="transition-colors hover:text-[var(--text-primary)]">Terms</a>
                </div>
            </x-layout.container>
        </footer>
    </x-layout.page>
</div>
</body>
</html>
