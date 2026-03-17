@props([
    'lines' => 3,
    'avatar' => false,
])

<x-ui.card class="p-4">
    <x-layout.stack space="4">
        @if ($avatar)
            <div class="flex items-center gap-3">
                <div class="skeleton h-10 w-10 rounded-full"></div>
                <div class="flex-1 space-y-2">
                    <div class="skeleton h-3 w-1/3"></div>
                    <div class="skeleton h-2.5 w-1/4"></div>
                </div>
            </div>
        @endif

        <x-layout.stack space="3">
            @foreach (range(1, $lines) as $line)
                <div class="skeleton h-3 {{ $line === 1 ? 'w-4/5' : ($line === $lines ? 'w-2/5' : 'w-full') }}"></div>
            @endforeach
        </x-layout.stack>
    </x-layout.stack>
</x-ui.card>
