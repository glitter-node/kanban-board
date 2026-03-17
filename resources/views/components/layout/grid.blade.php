@props([
    'cols' => '1',
    'md' => '2',
    'lg' => '3',
    'gap' => '6',
])

@php
    $baseCols = "layout-grid-cols-{$cols}";
    $mdCols = $md === null ? 'layout-grid-md-none' : "layout-grid-md-{$md}";
    $lgCols = $lg === null ? 'layout-grid-lg-none' : "layout-grid-lg-{$lg}";
    $gapClass = "layout-grid-gap-{$gap}";
@endphp

<div {{ $attributes->class(['layout-grid', $gapClass, $baseCols, $mdCols, $lgCols]) }}>
    {{ $slot }}
</div>
