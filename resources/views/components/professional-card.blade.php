@props(['professional', 'isFavorite' => false])

@php
    $hasReviews = (int) ($professional->total_reviews ?? 0) > 0;
    $publicAchievements = app(\App\Services\AchievementService::class)->getPublicAchievementsForProfessional($professional)->take(2);
@endphp

<article {{ $attributes->merge(['class' => 'marketplace-professional-card']) }}>
    <div class="d-flex align-items-start justify-content-between gap-3 mb-3"><x-ui.avatar :user="$professional->user" :src="$professional->profile_photo_url ?? $professional->profile_photo" :name="$professional->user?->name" size="lg" />@if ($professional->hasVerifiedIdentity())<x-ui.badge variant="verified" label="Identidad verificada" dot />@endif</div>
    <h2 class="marketplace-professional-card__name"><a href="{{ route('professional.public-profile', $professional) }}">{{ $professional->user?->name ?? 'Profesional Chambapp' }}</a></h2>
    <p class="marketplace-professional-card__location"><i class="bi bi-geo-alt" aria-hidden="true"></i> {{ collect([$professional->city, $professional->state])->filter()->join(', ') ?: 'Cerca de ti' }}</p>
    <p class="marketplace-professional-card__bio">{{ $professional->bio ?: 'Profesional listo para ayudarte.' }}</p>
    @if ($publicAchievements->isNotEmpty())
        <div class="d-flex flex-wrap gap-1 mb-2">
            @foreach ($publicAchievements as $ach)
                <span class="badge bg-light text-dark border d-inline-flex align-items-center" title="{{ $ach['description'] }}">
                    <i class="bi bi-{{ $ach['icon'] }} text-warning me-1"></i> {{ $ach['name'] }} · {{ $ach['level_label'] }}
                </span>
            @endforeach
        </div>
    @endif
    <div class="marketplace-professional-card__meta mb-3">@if ($hasReviews)<span><i class="bi bi-star-fill" aria-hidden="true"></i> {{ number_format((float) $professional->average_rating, 1) }} ({{ $professional->total_reviews }})</span>@else<span>Nuevo · Sin reseñas todavía</span>@endif</div>
    <div class="d-flex align-items-center justify-content-between gap-2"><a class="ui-button ui-button--outline ui-button--sm" href="{{ route('professional.public-profile', $professional) }}">Ver perfil</a>@auth @if (auth()->user()->isClient())<form method="POST" action="{{ route('professional.favorite.toggle', $professional) }}">@csrf<button class="favorite-button favorite-button--inline {{ $isFavorite ? 'is-favorite' : '' }}" type="submit" aria-label="{{ $isFavorite ? 'Quitar de favoritos' : 'Guardar profesional en favoritos' }}" aria-pressed="{{ $isFavorite ? 'true' : 'false' }}"><i class="bi bi-heart{{ $isFavorite ? '-fill' : '' }}" aria-hidden="true"></i></button></form>@endif @else<a class="favorite-button favorite-button--inline" href="{{ route('login') }}" aria-label="Inicia sesión para guardar favoritos"><i class="bi bi-heart" aria-hidden="true"></i></a>@endauth</div>
</article>
