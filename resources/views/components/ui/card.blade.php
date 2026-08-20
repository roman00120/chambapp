@props(['padding' => 'md'])

<article {{ $attributes->merge(['class' => 'ui-card ui-card--padding-'.$padding]) }}>
    @isset($header)
        <div class="ui-card__header">{{ $header }}</div>
    @endisset

    <div class="ui-card__body">{{ $slot }}</div>

    @isset($footer)
        <div class="ui-card__footer">{{ $footer }}</div>
    @endisset
</article>
