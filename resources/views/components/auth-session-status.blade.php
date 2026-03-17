@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'text-sm font-medium status-success']) }}>
        {{ $status }}
    </div>
@endif
