<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight">Experiments</h2>
    </x-slot>

    <x-ui.section class="py-12" width="7xl">
        <div class="grid gap-6 md:grid-cols-4">
            <x-ui.card>
                <p class="text-sm text-secondary">Total experiments</p>
                <p class="mt-2 text-3xl font-semibold">{{ $metrics['summary']['total'] }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-sm text-secondary">Running</p>
                <p class="mt-2 text-3xl font-semibold">{{ $metrics['summary']['running'] }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-sm text-secondary">Paused</p>
                <p class="mt-2 text-3xl font-semibold">{{ $metrics['summary']['paused'] }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-sm text-secondary">Completed</p>
                <p class="mt-2 text-3xl font-semibold">{{ $metrics['summary']['completed'] }}</p>
            </x-ui.card>
        </div>

        <div class="mt-6 space-y-6">
            @forelse ($metrics['experiments'] as $experiment)
                <x-ui.panel elevated="true">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="flex items-center gap-3">
                                <h3 class="text-lg font-semibold">{{ $experiment['name'] }}</h3>
                                <x-ui.badge>{{ $experiment['status'] }}</x-ui.badge>
                                @if ($experiment['sample_size_reached'])
                                    <x-ui.badge tone="success">Sample ready</x-ui.badge>
                                @endif
                            </div>
                            <p class="mt-2 text-sm text-secondary">{{ $experiment['key'] }}</p>
                            <p class="mt-1 text-sm text-secondary">Primary metric: {{ $experiment['primary_metric'] }}</p>
                            @if ($experiment['secondary_metrics'] !== [])
                                <p class="mt-1 text-sm text-secondary">Secondary metrics: {{ implode(', ', $experiment['secondary_metrics']) }}</p>
                            @endif
                        </div>

                        @if ($experiment['winner'])
                            <x-ui.surface class="p-4">
                                <p class="text-sm text-secondary">Current leader</p>
                                <p class="mt-2 text-lg font-semibold">Variant {{ $experiment['winner']['key'] }}</p>
                                <p class="mt-1 text-sm text-secondary">{{ $experiment['winner']['conversion_rate'] ?? 'N/A' }}% conversion</p>
                            </x-ui.surface>
                        @endif
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($experiment['variants'] as $variant)
                            <x-ui.card>
                                <div class="flex items-center justify-between">
                                    <h4 class="text-base font-semibold">Variant {{ $variant['key'] }}</h4>
                                    <x-ui.badge>{{ $variant['weight'] }}%</x-ui.badge>
                                </div>
                                <div class="mt-4 space-y-2 text-sm text-secondary">
                                    <p>Assignments: {{ $variant['assignments'] }}</p>
                                    <p>Primary events: {{ $variant['primary_events'] }}</p>
                                    <p>Conversion: {{ $variant['conversion_rate'] ?? 'N/A' }}%</p>
                                    <p>Engagement: {{ $variant['engagement_rate'] ?? 'N/A' }}%</p>
                                    <p>Drop-off: {{ $variant['drop_off_rate'] ?? 'N/A' }}%</p>
                                </div>

                                @if ($variant['secondary_metrics'] !== [])
                                    <div class="mt-4 border-t border-border pt-4 text-sm text-secondary">
                                        @foreach ($variant['secondary_metrics'] as $metric => $count)
                                            <p>{{ $metric }}: {{ $count }}</p>
                                        @endforeach
                                    </div>
                                @endif
                            </x-ui.card>
                        @endforeach
                    </div>
                </x-ui.panel>
            @empty
                <x-ui.state.empty
                    title="No experiments yet"
                    description="Create an experiment, add weighted variants, and start collecting outcome data."
                />
            @endforelse
        </div>
    </x-ui.section>
</x-app-layout>
