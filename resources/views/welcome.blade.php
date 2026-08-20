@extends('layouts.public')

@section('title', 'Chambapp | Encuentra al profesional perfecto para tu chamba')
@section('meta_description', 'Conecta con profesionales verificados cerca de ti. Rápido, seguro y confiable.')

@section('content')
    @php
        $verifiedCount = (int) data_get($homeStats, 'verified_professionals', 0);
        $completedCount = (int) data_get($homeStats, 'completed_jobs', 0);
        $averageRating = data_get($homeStats, 'average_rating');
        $reviewCount = (int) data_get($homeStats, 'total_reviews', 0);
        $serviceCount = (int) data_get($homeStats, 'active_services', 0);
        $availableCount = (int) data_get($homeStats, 'available_professionals', 0);
    @endphp
    <section id="inicio" class="hero-landing">
        <div class="hero-landing__wash" aria-hidden="true"></div>
        <div class="container hero-layout">
            <div class="hero-copy-column">
                <span class="hero-landing__kicker"><i class="bi bi-lightning-charge-fill" aria-hidden="true"></i> Chambas ahora, <b>soluciones al instante</b></span>
                <h1 class="hero-landing__title"><span class="hero-title-line">Encuentra al</span><span class="hero-title-line">profesional</span><span class="hero-title-line hero-title-line--accent">perfecto</span><span class="hero-title-line">para tu chamba</span></h1>
                <p class="hero-landing__copy">Conecta con profesionales verificados cerca de ti.<br>Rápido, seguro y confiable.</p>
                <div class="hero-landing__actions">@auth @if (auth()->user()->isClient())<x-ui.button href="{{ route('client.ondemand.create') }}">Solicitar chamba ahora <i class="bi bi-arrow-right" aria-hidden="true"></i></x-ui.button>@else<x-ui.button href="{{ route(auth()->user()->dashboardRoute()) }}">Ir a mi espacio <i class="bi bi-arrow-right" aria-hidden="true"></i></x-ui.button>@endif @else<x-ui.button href="{{ route('register') }}">Solicitar chamba ahora <i class="bi bi-arrow-right" aria-hidden="true"></i></x-ui.button>@endauth<a class="ui-button ui-button--outline" href="{{ route('register') }}">Soy profesional</a></div>
                <div class="hero-trust-grid" aria-label="Beneficios de Chambapp">
                    <article><span class="hero-trust-grid__icon hero-trust-grid__icon--warm"><i class="bi bi-patch-check" aria-hidden="true"></i></span><div><strong>Profesionales verificados</strong><small>Perfiles verificados y<br>calificaciones reales</small></div></article>
                    <article><span class="hero-trust-grid__icon hero-trust-grid__icon--blue"><i class="bi bi-bag-check" aria-hidden="true"></i></span><div><strong>Pagos seguros</strong><small>Paga de forma segura<br>con Mercado Pago</small></div></article>
                    <article><span class="hero-trust-grid__icon hero-trust-grid__icon--warm"><i class="bi bi-shield-check" aria-hidden="true"></i></span><div><strong>Garantía Chambapp</strong><small>Tu satisfacción es nuestra<br>prioridad</small></div></article>
                </div>
            </div>
            <div class="hero-stage">
                <div class="hero-stage__dots" aria-hidden="true"></div>
                <div class="hero-visual"><picture><img src="{{ asset('images/hero-professional.png') }}" width="1024" height="1536" fetchpriority="high" alt="Profesional Chambapp listo para ayudarte"></picture></div>
                <form method="GET" action="{{ route('marketplace.search') }}" class="hero-search-panel" aria-label="Buscar profesionales">
                    <div class="hero-search-panel__heading"><span class="hero-search-panel__mark"><img src="{{ asset('images/pwa/icon-192.png') }}" alt="" aria-hidden="true"></span><strong>¿Qué necesitas?</strong></div>
                    <div class="hero-search-panel__field"><label for="hero-category">Servicio</label><div class="hero-search-panel__control"><i class="bi bi-tools" aria-hidden="true"></i><select id="hero-category" name="category"><option value="">Selecciona una categoría</option>@foreach ($categories as $category)<option value="{{ $category->slug }}">{{ $category->name }}</option>@endforeach</select><i class="bi bi-chevron-down" aria-hidden="true"></i></div></div>
                    <div class="hero-search-panel__field"><label for="hero-location">Ubicación</label><div class="hero-search-panel__control"><i class="bi bi-geo-alt" aria-hidden="true"></i><input id="hero-location" name="city" type="search" placeholder="Mi ubicación actual" autocomplete="address-level2"><i class="bi bi-crosshair" aria-hidden="true"></i></div></div>
                    <button class="ui-button ui-button--primary w-100" type="submit">Buscar profesionales</button>
                </form>
                <div class="hero-proof-card">
                    <div class="hero-proof-card__avatars" aria-hidden="true">@forelse ($professionals->take(3) as $professional)<x-ui.avatar :user="$professional->user" :src="$professional->profile_photo" :name="$professional->user?->name" size="sm" />@empty<span><i class="bi bi-person-fill"></i></span><span><i class="bi bi-person-fill"></i></span><span><i class="bi bi-person-fill"></i></span>@endforelse</div>
                    <div><strong>{{ number_format($verifiedCount) }} profesionales</strong><small>verificados en Chambapp</small></div>
                    <span class="hero-proof-card__divider" aria-hidden="true"></span>
                    <div><strong><i class="bi bi-star-fill" aria-hidden="true"></i> {{ $averageRating !== null ? number_format((float) $averageRating, 1).'/5' : 'Sin reseñas' }}</strong><small>{{ $reviewCount > 0 ? number_format($reviewCount).' calificaciones reales' : 'Calificaciones verificadas' }}</small></div>
                </div>
                @if ($availableCount > 0)<div class="hero-availability"><span></span>{{ number_format($availableCount) }} profesionales disponibles cerca de ti</div>@endif
            </div>
        </div>
        <div class="container hero-metrics" aria-label="Estadísticas de Chambapp">
            <article><span class="hero-metric__icon hero-metric__icon--orange"><i class="bi bi-people" aria-hidden="true"></i></span><div><strong>{{ number_format($verifiedCount) }}</strong><small>Profesionales verificados</small></div></article>
            <article><span class="hero-metric__icon hero-metric__icon--blue"><i class="bi bi-clipboard2-check" aria-hidden="true"></i></span><div><strong>{{ number_format($completedCount) }}</strong><small>Chambas completadas</small></div></article>
            <article><span class="hero-metric__icon hero-metric__icon--yellow"><i class="bi bi-star" aria-hidden="true"></i></span><div><strong>{{ $averageRating !== null ? number_format((float) $averageRating, 1).'/5' : '—/5' }}</strong><small>Calificación promedio</small></div></article>
            <article><span class="hero-metric__icon hero-metric__icon--green"><i class="bi bi-tools" aria-hidden="true"></i></span><div><strong>{{ number_format($serviceCount) }}</strong><small>Servicios disponibles</small></div></article>
        </div>
    </section>

    <section id="categorias" class="reference-section"><div class="container"><div class="reference-heading"><div><span class="reference-heading__eyebrow">Explora</span><h2>Categorías populares</h2><p>Encuentra profesionales en las categorías más solicitadas</p></div><a href="{{ route('marketplace.categories') }}">Ver todas <i class="bi bi-arrow-up-right"></i></a></div><div class="row g-3">@forelse ($categories as $category)<div class="col-6 col-md-4 col-lg-2"><a class="reference-category" href="{{ route('marketplace.category', $category) }}"><span><i class="bi bi-{{ $category->icon ?: 'grid' }}"></i></span><strong>{{ $category->name }}</strong><small>Profesionales cerca</small></a></div>@empty<div class="col-12"><x-ui.empty-state icon="grid" title="Categorías en preparación" description="Pronto podrás explorar servicios por categoría." /></div>@endforelse</div></div></section>

    <section id="profesionales" class="reference-section reference-section--muted"><div class="container"><div class="reference-heading"><div><span class="reference-heading__eyebrow">Talento confiable</span><h2>Profesionales destacados</h2><p>Personas verificadas que harán posible tu próxima solución</p></div><a href="{{ route('marketplace.search') }}">Buscar profesionales <i class="bi bi-arrow-right"></i></a></div><div class="row g-3 g-lg-4">@forelse ($professionals as $professional)<div class="col-12 col-md-6 col-lg-4"><x-ui.card class="reference-professional-card h-100" padding="lg"><div class="reference-professional-card__top"><x-ui.avatar :user="$professional->user" :src="$professional->profile_photo" :name="$professional->user?->name" size="lg" /><x-ui.badge variant="verified" label="Verificado" /></div><h3>{{ $professional->user?->name ?? 'Profesional Chambapp' }}</h3><p>{{ $professional->bio ?: 'Listo para ayudarte con experiencia y atención cercana.' }}</p><div class="reference-professional-card__meta"><span><i class="bi bi-star-fill"></i> {{ (int) $professional->total_reviews > 0 ? number_format((float) $professional->average_rating, 1) : 'Nuevo' }}</span><span><i class="bi bi-geo-alt"></i> {{ $professional->city ?: 'Cerca de ti' }}</span></div><x-ui.button variant="outline" class="w-100 mt-auto" href="{{ route('professional.public-profile', $professional) }}">Ver perfil</x-ui.button></x-ui.card></div>@empty<div class="col-12"><x-ui.empty-state icon="person-badge" title="Profesionales en preparación" description="Estamos preparando perfiles para que puedas conocerlos." /></div>@endforelse</div></div></section>

    <section id="como-funciona" class="reference-section"><div class="container"><div class="reference-how"><div><span class="reference-heading__eyebrow">Cómo funciona</span><h2>Tu próxima chamba empieza aquí</h2><p>Encuentra, compara y conecta con la persona ideal para resolverlo.</p></div><div class="reference-steps"><div><b>01</b><strong>Cuéntanos qué necesitas</strong><small>Publica tu solicitud en minutos.</small></div><div><b>02</b><strong>Recibe opciones confiables</strong><small>Conoce perfiles y cotizaciones.</small></div><div><b>03</b><strong>Haz que suceda</strong><small>Contrata y da seguimiento.</small></div></div></div></div></section>

    <section class="reference-cta"><div class="container"><div class="reference-cta__inner"><div><span class="reference-heading__eyebrow">Para profesionales</span><h2>¿Tienes una habilidad que compartir?</h2><p>Crea tu perfil, publica tus servicios y encuentra nuevas chambas.</p></div>@guest<x-ui.button href="{{ route('register') }}">Crear cuenta profesional <i class="bi bi-arrow-right"></i></x-ui.button>@else<x-ui.button href="{{ route(auth()->user()->dashboardRoute()) }}">Ir a mi espacio <i class="bi bi-arrow-right"></i></x-ui.button>@endguest</div></div></section>
@endsection
