@props(['professional', 'isFavorite' => false])

@php
    $hasReviews = (int) ($professional->total_reviews ?? 0) > 0;
@endphp

<article {{ $attributes->merge(['class' => 'marketplace-professional-card']) }}>
    <div class="d-flex align-items-start justify-content-between gap-3 mb-3"><x-ui.avatar :user="$professional->user" :src="$professional->profile_photo" :name="$professional->user?->name" size="lg" />@if ($professional->verification_status?->value === 'verified')<x-ui.badge variant="verified" label="Verificado" dot />@endif</div>
    <h2 class="marketplace-professional-card__name"><a href="{{ route('professional.public-profile', $professional) }}">{{ $professional->user?->name ?? 'Profesional Chambapp' }}</a></h2>
    <p class="marketplace-professional-card__location"><i class="bi bi-geo-alt" aria-hidden="true"></i> {{ collect([$professional->city, $professional->state])->filter()->join(', ') ?: 'Cerca de ti' }}</p>
    <p class="marketplace-professional-card__bio">{{ $professional->bio ?: 'Profesional listo para ayudarte.' }}</p>
    <div class="marketplace-professional-card__meta mb-3">@if ($hasReviews)<span><i class="bi bi-star-fill" aria-hidden="true"></i> {{ number_format((float) $professional->average_rating, 1) }} ({{ $professional->total_reviews }})</span>@else<span>Nuevo · Sin reseñas todavía</span>@endif</div>
    <div class="d-flex align-items-center justify-content-between gap-2"><a class="ui-button ui-button--outline ui-button--sm" href="{{ route('professional.public-profile', $professional) }}">Ver perfil</a>@auth @if (auth()->user()->isClient())<form method="POST" action="{{ route('professional.favorite.toggle', $professional) }}">@csrf<button class="favorite-button favorite-button--inline {{ $isFavorite ? 'is-favorite' : '' }}" type="submit" aria-label="{{ $isFavorite ? 'Quitar de favoritos' : 'Guardar profesional en favoritos' }}" aria-pressed="{{ $isFavorite ? 'true' : 'false' }}"><i class="bi bi-heart{{ $isFavorite ? '-fill' : '' }}" aria-hidden="true"></i></button></form>@endif @else<a class="favorite-button favorite-button--inline" href="{{ route('login') }}" aria-label="Inicia sesión para guardar favoritos"><i class="bi bi-heart" aria-hidden="true"></i></a>@endauth</div>
</article>
