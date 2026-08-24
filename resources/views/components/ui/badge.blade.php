@props(['variant' => 'neutral', 'label' => null])

@php($displayLabel = $label === 'Verificado' ? 'Perfil habilitado' : $label)
<span {{ $attributes->merge(['class' => 'ui-badge ui-badge--'.$variant]) }}>
    <span class="ui-badge__dot" aria-hidden="true"></span>
    {{ $displayLabel ?? $slot }}
</span>
