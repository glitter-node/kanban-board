@props([
    'orientation' => 'horizontal',
])

@if ($orientation === 'vertical')
    <div {{ $attributes->class(['layout-divider-vertical']) }} aria-hidden="true"></div>
@else
    <div {{ $attributes->class(['layout-divider']) }} aria-hidden="true"></div>
@endif
