<a {{ $attributes->merge(['class' => 'brand-mark']) }} href="{{ route('home') }}" aria-label="Chambapp, inicio">
    <span class="brand-mark__image-wrap">
        <img class="brand-mark__image" src="{{ asset('images/chambapp-logo.jpeg') }}" alt="Chambapp">
    </span>
    <span class="brand-mark__name">Chambapp</span>
</a>
