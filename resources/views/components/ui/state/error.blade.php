@props([
    'title' => 'Something went wrong',
    'message' => 'We could not load this content.',
    'retryLabel' => null,
])

<x-ui.card class="p-6">
    <x-layout.stack class="items-center text-center" space="4">
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-error text-error-foreground ring-1 ring-error">
            <span class="text-lg">!</span>
        </div>
        <x-layout.stack space="2">
            <h3 class="text-base font-semibold text-foreground">{{ $title }}</h3>
            @if (trim((string) $slot) !== '')
                <div class="max-w-md text-sm text-muted-foreground">
                    {{ $slot }}
                </div>
            @else
                <p class="max-w-md text-sm text-muted-foreground">{{ $message }}</p>
            @endif
        </x-layout.stack>
        @if ($retryLabel)
            <x-ui.button variant="secondary" size="sm" :type="'button'" {{ $attributes }}>
                {{ $retryLabel }}
            </x-ui.button>
        @elseif (isset($action))
            {{ $action }}
        @endif
    </x-layout.stack>
</x-ui.card>
