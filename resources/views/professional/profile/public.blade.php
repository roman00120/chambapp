@extends('layouts.public')

@section('title', $profile->user->name.' | Chambapp')
@section('meta_description', 'Perfil público de '.$profile->user->name.' en Chambapp.')

@section('content')
    <section class="marketplace-page professional-public-page profile-theme--{{ $profile->profile_theme }} profile-banner--{{ $profile->profile_banner }} profile-frame--{{ $profile->profile_frame }} profile-animation--{{ $profile->profile_animation }}" style="--profile-accent: {{ $profile->profile_accent }}">
        <div class="container">
            <nav class="breadcrumb marketplace-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Inicio</a><span>/</span><strong>Perfil profesional</strong></nav>
            <div class="row g-4 g-lg-5">
                <div class="col-12 col-lg-4">
                    <x-ui.card class="marketplace-professional-card h-100" padding="lg">
                        <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                            <x-ui.avatar :user="$profile->user" :src="$profile->profile_photo_url ?? $profile->profile_photo" :name="$profile->user->name" size="xl" />
                            @if ($profile->hasVerifiedIdentity())
                                <x-ui.badge variant="verified" label="Identidad verificada" dot />
                            @endif
                        </div>
                        <h1 class="page-title h2">{{ $profile->user->name }}</h1>
                        <p class="marketplace-professional-card__location"><i class="bi bi-geo-alt" aria-hidden="true"></i> {{ collect([$profile->city, $profile->state])->filter()->join(', ') ?: 'Ubicación no especificada' }}</p>
                        <p class="marketplace-professional-card__bio">{{ $profile->bio ?: 'Profesional listo para ayudarte.' }}</p>
                        @if ($profile->hasVerifiedIdentity())
                            <p class="small text-muted">Identidad verificada significa que se completó el proceso de comprobación de identidad de Chambapp. No constituye garantía sobre la calidad, licencias, antecedentes o resultado del servicio.</p>
                        @endif
                        <div class="marketplace-professional-card__meta mb-4"><span><i class="bi bi-briefcase" aria-hidden="true"></i> {{ $profile->experience_years }} años de experiencia</span><span><i class="bi bi-star-fill" aria-hidden="true"></i> {{ (int) $profile->total_reviews > 0 ? number_format((float) $profile->average_rating, 1).' ('.$profile->total_reviews.')' : 'Sin reseñas todavía' }}</span><span><i class="bi bi-check2-circle" aria-hidden="true"></i> {{ $profile->total_completed_jobs }} trabajos completados</span></div>
                        @auth @if (auth()->user()->isClient())<form method="POST" action="{{ route('professional.favorite.toggle', $profile) }}">@csrf<button class="ui-button ui-button--outline w-100" type="submit"><i class="bi bi-heart{{ $isFavorite ? '-fill' : '' }}" aria-hidden="true"></i> {{ $isFavorite ? 'Quitar de favoritos' : 'Guardar en favoritos' }}</button></form>@endif @else<a class="ui-button ui-button--outline w-100" href="{{ route('login') }}"><i class="bi bi-heart" aria-hidden="true"></i> Inicia sesión para guardar</a>@endauth
                    </x-ui.card>
                </div>
                <div class="col-12 col-lg-8"><div class="d-flex align-items-end justify-content-between gap-3 mb-3"><div><p class="eyebrow mb-2">Servicios publicados</p><h2 class="section-title mb-0">Lo que puede hacer por ti</h2></div><span class="text-muted small">{{ $profile->services->count() }} disponibles</span></div><div class="row g-3">@forelse ($profile->services as $service)<div class="col-12 col-md-6"><x-service-card :service="$service" :is-favorite="$isFavorite" /></div>@empty<div class="col-12"><x-ui.empty-state icon="bi-tools" title="Aún no hay servicios publicados" description="Este profesional todavía no tiene servicios activos para mostrar." /></div>@endforelse</div></div>
            </div>
            <div class="row g-4 mt-1"><div class="col-12 col-lg-8"><div class="d-flex align-items-end justify-content-between gap-3 mb-3"><div><p class="eyebrow mb-2">Experiencias confirmadas</p><h2 class="section-title mb-0">Reseñas recientes</h2></div>@if ($profile->total_reviews > 5)<a class="text-link" href="{{ route('reviews.index', $profile) }}">Ver todas <i class="bi bi-arrow-right" aria-hidden="true"></i></a>@endif</div><div class="review-list">@forelse ($profile->reviews as $review)<x-review-card :review="$review" :reportable="true" />@empty<x-ui.empty-state icon="bi-star" title="Este profesional todavía no tiene reseñas." description="Las opiniones aparecerán después de trabajos completados en Chambapp." />@endforelse</div></div></div>
        </div>
    </section>
@endsection
