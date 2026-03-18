<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight">Analytics</h2>
            <x-ui.badge>{{ $metrics['summary']['events_total'] }} events</x-ui.badge>
        </div>
    </x-slot>

    <x-ui.section class="py-12" width="7xl">
        <x-layout.grid :lg="null" class="gap-6 md:grid-cols-2 lg:grid-cols-4">
            <x-ui.card class="p-5">
                <p class="ui-meta">Task success rate</p>
                <p class="mt-2 text-2xl font-semibold">{{ $metrics['summary']['task_success_rate'] ?? 'N/A' }}@if($metrics['summary']['task_success_rate'] !== null)%@endif</p>
            </x-ui.card>
            <x-ui.card class="p-5">
                <p class="ui-meta">Time to first action</p>
                <p class="mt-2 text-2xl font-semibold">{{ $metrics['summary']['time_to_first_action_seconds'] ?? 'N/A' }}</p>
            </x-ui.card>
            <x-ui.card class="p-5">
                <p class="ui-meta">Drag usage rate</p>
                <p class="mt-2 text-2xl font-semibold">{{ $metrics['summary']['drag_usage_rate'] ?? 'N/A' }}@if($metrics['summary']['drag_usage_rate'] !== null)%@endif</p>
            </x-ui.card>
            <x-ui.card class="p-5">
                <p class="ui-meta">Average cycle time</p>
                <p class="mt-2 text-2xl font-semibold">{{ $metrics['summary']['average_cycle_time_hours'] ?? 'N/A' }}h</p>
            </x-ui.card>
        </x-layout.grid>

        <x-layout.grid :lg="null" class="mt-6 gap-6 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
            <x-ui.panel>
                <div class="ui-panel-header">
                    <h3 class="ui-title">Top actions</h3>
                </div>
                <div class="ui-panel-body space-y-3">
                    @foreach ($metrics['top_actions'] as $item)
                        <x-ui.surface class="flex items-center justify-between p-4">
                            <span>{{ $item['event_name'] }}</span>
                            <x-ui.badge>{{ $item['count'] }}</x-ui.badge>
                        </x-ui.surface>
                    @endforeach
                </div>
            </x-ui.panel>

            <x-ui.panel>
                <div class="ui-panel-header">
                    <h3 class="ui-title">Feature adoption</h3>
                </div>
                <div class="ui-panel-body space-y-3">
                    @foreach ($metrics['feature_adoption'] as $feature => $count)
                        <x-ui.surface class="flex items-center justify-between p-4">
                            <span>{{ ucfirst($feature) }}</span>
                            <x-ui.badge>{{ $count }}</x-ui.badge>
                        </x-ui.surface>
                    @endforeach
                </div>
            </x-ui.panel>
        </x-layout.grid>

        <x-layout.grid :lg="null" class="mt-6 gap-6 lg:grid-cols-2">
            <x-ui.panel>
                <div class="ui-panel-header">
                    <h3 class="ui-title">Funnel</h3>
                </div>
                <div class="ui-panel-body space-y-3">
                    @foreach ($metrics['funnel'] as $step)
                        <x-ui.surface class="flex items-center justify-between p-4">
                            <span>{{ $step['label'] }}</span>
                            <x-ui.badge>{{ $step['count'] }}</x-ui.badge>
                        </x-ui.surface>
                    @endforeach
                </div>
            </x-ui.panel>

            <x-ui.panel>
                <div class="ui-panel-header">
                    <h3 class="ui-title">Session insights</h3>
                </div>
                <div class="ui-panel-body space-y-3">
                    <x-ui.surface class="flex items-center justify-between p-4">
                        <span>Sessions total</span>
                        <x-ui.badge>{{ $metrics['summary']['sessions_total'] }}</x-ui.badge>
                    </x-ui.surface>
                    <x-ui.surface class="flex items-center justify-between p-4">
                        <span>Average actions per session</span>
                        <x-ui.badge>{{ $metrics['session_insights']['average_actions_per_session'] ?? 'N/A' }}</x-ui.badge>
                    </x-ui.surface>
                    <x-ui.surface class="flex items-center justify-between p-4">
                        <span>Average session duration</span>
                        <x-ui.badge>{{ $metrics['session_insights']['average_session_duration_seconds'] ?? 'N/A' }}s</x-ui.badge>
                    </x-ui.surface>
                    <x-ui.surface class="flex items-center justify-between p-4">
                        <span>Returning users</span>
                        <x-ui.badge>{{ $metrics['summary']['returning_users'] }}</x-ui.badge>
                    </x-ui.surface>
                </div>
            </x-ui.panel>
        </x-layout.grid>

        <x-layout.grid :lg="null" class="mt-6 gap-6 lg:grid-cols-2">
            <x-ui.panel>
                <div class="ui-panel-header">
                    <h3 class="ui-title">UX issues</h3>
                </div>
                <div class="ui-panel-body space-y-3">
                    @forelse ($metrics['ux_issues'] as $issue)
                        <x-ui.surface class="flex items-center justify-between p-4">
                            <span>{{ str_replace('_', ' ', $issue['issue']) }}</span>
                            <x-ui.badge tone="warning">{{ $issue['count'] }}</x-ui.badge>
                        </x-ui.surface>
                    @empty
                        <x-ui.state.empty title="No UX issues detected" description="No tracked anomalies have been recorded yet." />
                    @endforelse
                </div>
            </x-ui.panel>

            <x-ui.panel>
                <div class="ui-panel-header">
                    <h3 class="ui-title">Blocked tasks</h3>
                </div>
                <div class="ui-panel-body space-y-3">
                    @forelse ($metrics['blocked_tasks'] as $task)
                        <x-ui.surface class="flex items-center justify-between p-4">
                            <span>Card {{ $task['card_id'] }}</span>
                            <x-ui.badge tone="error">{{ $task['hours_blocked'] }}h</x-ui.badge>
                        </x-ui.surface>
                    @empty
                        <x-ui.state.empty title="No blocked tasks" description="No blocked-task pattern has been detected yet." />
                    @endforelse
                </div>
            </x-ui.panel>
        </x-layout.grid>

        <x-ui.panel class="mt-6">
            <div class="ui-panel-header">
                <h3 class="ui-title">Recent feedback</h3>
            </div>
            <div class="ui-panel-body space-y-3">
                @forelse ($metrics['recent_feedback'] as $feedback)
                    <x-ui.surface class="p-4">
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-medium">{{ $feedback->payload['sentiment'] ?? 'neutral' }}</span>
                            <span class="ui-meta">{{ $feedback->created_at?->diffForHumans() }}</span>
                        </div>
                        @if (! empty($feedback->payload['comment']))
                            <p class="mt-2 text-sm text-secondary">{{ $feedback->payload['comment'] }}</p>
                        @endif
                    </x-ui.surface>
                @empty
                    <x-ui.state.empty title="No feedback yet" description="Feedback submissions will appear here." />
                @endforelse
            </div>
        </x-ui.panel>
    </x-ui.section>
</x-app-layout>
