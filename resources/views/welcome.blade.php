<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-experiments" content='@json($frontendExperiments ?? [])'>
    <meta name="description" content="A realtime board that reveals blocked work, ownership gaps, and stalled progress so teams can keep work moving without status chasing.">
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
                    <a href="{{ route('login') }}" class="transition-colors hover:text-[var(--text-primary)]">Sign in</a>
                </nav>
            </x-layout.container>
        </header>

        <main>
            <x-ui.section id="hero" class="pb-24 pt-10 lg:pb-32 lg:pt-12" width="7xl">
                    <x-layout.grid :lg="null" class="gap-14 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)]">
                        <div class="max-w-2xl">
                            <div class="inline-flex items-center gap-2 rounded-full border border-[var(--border)] bg-[var(--surface-elevated)] px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--text-primary)]">
                                <span class="h-2 w-2 rounded-full bg-[var(--primary)]"></span>
                                Realtime flow visibility
                            </div>

                            <h1 class="mt-8 max-w-3xl text-5xl font-semibold tracking-tight text-[var(--text-primary)] sm:text-6xl">
                                See where work is stuck. Move it forward.
                            </h1>

                            <p class="mt-6 max-w-2xl text-lg leading-8 text-[var(--text-secondary)]">
                                A realtime board that makes blockers, ownership, and progress visible without status chasing.
                            </p>

                            <div class="mt-10 flex flex-col gap-3 sm:flex-row">
                                @experiment('landing_primary_cta')
                                    @variant('A')
                                        <x-ui.button as="a" href="{{ route('register') }}" variant="primary" size="lg" class="rounded-2xl normal-case tracking-normal">
                                            Find what's blocking your work
                                        </x-ui.button>
                                    @endvariant
                                    @variant('B')
                                        <x-ui.button as="a" href="{{ route('register') }}" variant="primary" size="lg" class="rounded-2xl normal-case tracking-normal">
                                            See where work is stuck
                                        </x-ui.button>
                                    @endvariant
                                @endexperiment
                                <x-ui.button as="a" href="#preview" variant="secondary" size="lg" class="rounded-2xl normal-case tracking-normal">
                                    See the board in motion
                                </x-ui.button>
                            </div>

                            <div class="mt-12 grid gap-4 sm:grid-cols-3">
                                <x-ui.card class="p-4">
                                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--text-secondary)]">Blocked work</p>
                                    <p class="mt-2 text-2xl font-semibold text-[var(--text-primary)]">Visible</p>
                                </x-ui.card>
                                <x-ui.card class="p-4">
                                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--text-secondary)]">Ownership</p>
                                    <p class="mt-2 text-2xl font-semibold text-[var(--text-primary)]">Clear</p>
                                </x-ui.card>
                                <x-ui.card class="p-4">
                                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--text-secondary)]">Movement</p>
                                    <p class="mt-2 text-2xl font-semibold text-[var(--text-primary)]">Immediate</p>
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

            <x-ui.section id="stuck-exposure" class="section border-y border-[var(--border)] bg-[var(--surface)]" width="xl">
                    <div id="features" class="section-inner">
                        <p class="section-label ui-kicker text-[var(--primary)]">This is where work stalls</p>
                        <h2 class="section-title">Work doesn&apos;t stop in one place. It slows, waits, and slips between handoffs.</h2>
                    </div>

                    <x-ui.surface variant="elevated" class="mt-10 p-6">
                        <div class="stuck-list">
                            <p>Waiting on someone who thinks it is not theirs</p>
                            <p>Marked in progress but not touched</p>
                            <p>Blocked, but no one calls it out</p>
                            <p>Ownership moved, but the work did not</p>
                            <p>Discussion happened outside, so the task lost context</p>
                        </div>

                        <div class="mt-8 flex flex-col items-start gap-4">
                            <p class="section-bridge">
                                Nothing is broken. It is just not moving.
                            </p>
                            <x-ui.button as="a" href="/pre-verify/request" variant="primary" size="lg" class="cta-primary rounded-2xl normal-case tracking-normal">
                                See what&apos;s already blocked
                            </x-ui.button>
                        </div>
                    </x-ui.surface>
            </x-ui.section>

            <x-ui.section id="flow-resolution" class="section" width="xl">
                    <div class="section-inner">
                        <p class="section-label ui-kicker text-[var(--primary)]">Make work move again</p>
                        <h2 class="section-title">When work is stuck, you need to see it, assign it, and move it.</h2>
                    </div>

                    <div class="flow-grid mt-10">
                        @php
                            $features = [
                                ['step' => '01', 'title' => 'See ownership clearly', 'text' => 'Every task shows who owns the next move.'],
                                ['step' => '02', 'title' => 'Spot blockers instantly', 'text' => 'Work that is stuck stands out without digging.'],
                                ['step' => '03', 'title' => 'Unstick work fast', 'text' => 'Move tasks before waiting turns into delay.'],
                                ['step' => '04', 'title' => 'Carry decisions to done', 'text' => 'Context stays with the task across every handoff.'],
                            ];
                        @endphp

                        @foreach ($features as $feature)
                            <article class="flow-item">
                                <div class="text-[var(--primary)]">
                                    <div class="ui-glow flex h-12 w-12 items-center justify-center rounded-2xl border border-[var(--border)] bg-[var(--surface-elevated)] text-sm font-semibold">
                                        {{ $feature['step'] }}
                                    </div>
                                </div>
                                <div class="mt-5 max-w-2xl">
                                    <h3>{{ $feature['title'] }}</h3>
                                    <p>{{ $feature['text'] }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="mt-8 flex flex-col items-start gap-4">
                        <p class="section-bridge">
                            Once you see where work is stuck, you can move it immediately.
                        </p>
                        <x-ui.button as="a" href="/pre-verify/request" variant="primary" size="lg" class="cta-primary rounded-2xl normal-case tracking-normal">
                            See what&apos;s already blocked
                        </x-ui.button>
                    </div>
            </x-ui.section>

            <x-ui.section id="preview" class="section" width="xl">
                    <div class="section-inner max-w-2xl">
                        <p class="section-label ui-kicker text-[var(--primary)]">See what is stuck</p>
                        <h2 class="section-title">This is where blocked work becomes visible</h2>
                        <p class="section-bridge mt-4">
                            See ownership, waiting work, and stalled tasks in one place.
                        </p>
                    </div>

                    <x-ui.surface variant="elevated" class="mt-10 rounded-2xl p-5">
                        <x-ui.surface class="rounded-[1.8rem] p-5">
                            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.2fr)_minmax(18rem,0.8fr)]">
                                <x-ui.surface class="overflow-hidden rounded-[1.6rem]">
                                    <div class="flex items-center justify-between border-b border-[var(--border)] px-5 py-4">
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.18em] text-[var(--text-secondary)]">Where work gets exposed</p>
                                            <h3 class="mt-1 text-lg font-semibold text-[var(--text-primary)]">See blocked work, owners, and waiting handoffs</h3>
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
                                        <p class="text-xs uppercase tracking-[0.18em] text-[var(--text-secondary)]">What becomes visible</p>
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
                                        <p class="text-sm font-semibold">This is how stuck work becomes visible enough to move.</p>
                                    </div>
                                </div>
                            </div>
                        </x-ui.surface>
                    </x-ui.surface>
            </x-ui.section>

            <x-ui.section id="final-cta" class="section pb-24 pt-8" width="lg">
                    <x-ui.card class="section-inner flex flex-col items-center rounded-[2.4rem] p-8 text-center sm:p-10">
                        <div>
                            <p class="section-label ui-kicker text-[var(--primary)]">Final CTA</p>
                            <h2 class="section-title">Work is already waiting. You just haven&apos;t seen it yet.</h2>
                        </div>

                        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                            <x-ui.button as="a" href="/pre-verify/request" variant="primary" size="lg" class="cta-primary rounded-2xl normal-case tracking-normal">
                                See what&apos;s already blocked
                            </x-ui.button>
                        </div>
                    </x-ui.card>
            </x-ui.section>
        </main>

        <x-layout.divider class="bg-border" />

        <footer class="py-10 text-[var(--text-secondary)]">
            <x-layout.container class="flex flex-col items-center justify-between gap-5 text-sm text-[var(--text-secondary)] md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <span>Board that reveals blocked work and keeps it moving.</span>
                    <a href="https://glitter.kr" target="_blank" rel="noopener noreferrer" aria-label="Visit Glitter.kr" class="transition-colors hover:text-[var(--text-primary)]">
                        Glitter.kr
                    </a>
                </div>

                <div class="flex gap-6 flex-wrap">
                    <a href="#features" class="transition-colors hover:text-[var(--text-primary)]">Features</a>
                    <a href="#preview" class="transition-colors hover:text-[var(--text-primary)]">Preview</a>
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
