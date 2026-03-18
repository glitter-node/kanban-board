<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="description" content="Organize work with simple realtime boards. Collaborative Kanban for teams and individuals built with Laravel.">
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
<span class="grid h-10 w-10 place-items-center rounded-2xl bg-surface border border-border">
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
<div class="inline-flex items-center gap-2 rounded-full border border-primary bg-primary px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-primary-foreground text-white">
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
<a href="{{ route('register') }}" class="btn-primary rounded-2xl px-6 py-3 text-sm normal-case tracking-normal">
Create a board
</a>
<a href="#preview" class="btn-secondary rounded-2xl px-6 py-3 text-sm normal-case tracking-normal">
View demo
</a>
</div>
</div>

<div class="relative">
<div class="ui-surface-elevated rounded-[2rem] p-4">
<div class="ui-gradient ui-surface rounded-[1.7rem] p-5">
<p class="text-secondary">Board preview</p>
</div>
</div>
</div>

</x-layout.grid>
</x-layout.container>
</x-layout.section>
</main>

<x-layout.divider class="bg-border" />

<footer class="py-10">
<x-layout.container class="flex flex-col gap-5 text-sm text-secondary md:flex-row md:items-center md:justify-between">

<div class="flex flex-wrap items-center gap-3">
<p>Board for collaborative Kanban workflow.</p>
<a href="https://glitter.kr"
   target="_blank"
   rel="noopener noreferrer"
   aria-label="Visit Glitter.kr"
   class="text-zinc-500 transition-colors hover:text-zinc-300">
Glitter.kr
</a>
</div>

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
