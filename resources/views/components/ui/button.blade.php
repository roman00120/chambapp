@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
    $classes = trim('ui-button ui-button--'.$variant.' '.($size !== 'md' ? 'ui-button--'.$size : ''));
@endphp

@if ($href)
    <a {{ $attributes->merge(['class' => $classes, 'href' => $href]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['class' => $classes, 'type' => $type]) }}>{{ $slot }}</button>
@endif
