@props(['variant' => 'neutral', 'label' => null])

<span {{ $attributes->merge(['class' => 'ui-badge ui-badge--'.$variant]) }}>
    <span class="ui-badge__dot" aria-hidden="true"></span>
    {{ $label ?? $slot }}
</span>
