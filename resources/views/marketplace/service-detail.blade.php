@extends('layouts.public')

@section('title', $service->title.' | Chambapp')
@section('meta_description', \Illuminate\Support\Str::limit($service->description, 155))

@section('content')
    <section class="marketplace-page">
        <div class="container">
            <nav class="breadcrumb marketplace-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Inicio</a>
                <span>/</span>
                <a href="{{ route('marketplace.search', ['category' => $service->category->slug]) }}">{{ $service->category->name }}</a>
                <span>/</span>
                <strong>{{ $service->title }}</strong>
            </nav>

            <div class="row g-4 g-lg-5">
                {{-- Left Column: Visual Media & Full Details --}}
                <div class="col-12 col-lg-7">
                    @if ($service->images->isNotEmpty())
                        <div id="service-gallery-{{ $service->id }}" class="carousel slide service-gallery" data-bs-ride="false">
                            <div class="carousel-inner">
                                @foreach ($service->images->sortBy('sort_order') as $image)
                                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->path) }}" class="service-gallery__image" alt="{{ $image->alt_text ?: $service->title }}" @if (!$loop->first) loading="lazy" @endif>
                                    </div>
                                @endforeach
                            </div>
                            @if ($service->images->count() > 1)
                                <button class="carousel-control-prev" type="button" data-bs-target="#service-gallery-{{ $service->id }}" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Anterior</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#service-gallery-{{ $service->id }}" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Siguiente</span>
                                </button>
                            @endif
                        </div>
                    @else
                        <div class="service-gallery service-gallery--empty">
                            <i class="bi bi-tools" aria-hidden="true"></i>
                            <span>Este servicio aún no tiene imágenes.</span>
                        </div>
                    @endif

                    {{-- Description Section --}}
                    <div class="service-detail__description-card">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-card-text text-primary fs-5" aria-hidden="true"></i>
                            <h2 class="h5 mb-0 fw-bold">Sobre este servicio</h2>
                        </div>
                        <p class="service-detail__description mb-0">{{ $service->description }}</p>
                    </div>

                    {{-- Trust Guarantees Grid --}}
                    <div class="service-detail__trust-grid">
                        <div class="service-detail__trust-item">
                            <i class="bi bi-lightning-charge-fill" aria-hidden="true"></i>
                            <div>
                                <span class="d-block text-dark fw-bold">Contratación directa</span>
                                <span class="small text-muted">Sin cotizaciones ni esperas</span>
                            </div>
                        </div>
                        <div class="service-detail__trust-item">
                            <i class="bi bi-shield-check" aria-hidden="true"></i>
                            <div>
                                <span class="d-block text-dark fw-bold">Pago 100% protegido</span>
                                <span class="small text-muted">Mercado Pago en custodia</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Sticky Summary & Direct Hire Action --}}
                <div class="col-12 col-lg-5">
                    <div class="service-detail__sticky-card">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <span class="service-card__category mb-0">{{ $service->category->name }}</span>
                            <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle px-2 py-1 small rounded-pill">
                                <i class="bi bi-check-circle-fill me-1" aria-hidden="true"></i> Disponible
                            </span>
                        </div>

                        <h1 class="service-detail__title mb-2">{{ $service->title }}</h1>

                        <div class="service-detail__price-box">
                            <div>
                                <span class="small text-muted d-block fw-semibold">Precio del servicio</span>
                                <div class="service-detail__price-value">{{ $service->formattedPrice() }}</div>
                            </div>
                            <span class="badge bg-light text-muted border small">Tarifa fija</span>
                        </div>

                        {{-- Professional Card --}}
                        <div class="service-detail__pro-card">
                            <x-ui.avatar :user="$service->professional->user" :src="$service->professional->profilePhotoUrl()" :name="$service->professional->user->name" size="lg" />
                            <div class="service-detail__pro-info">
                                <span class="small text-muted d-block">Ofrecido por</span>
                                <a class="service-detail__pro-name" href="{{ route('professional.public-profile', $service->professional) }}">
                                    {{ $service->professional->user->name }}
                                </a>
                                <div class="service-detail__facts">
                                    <span><i class="bi bi-geo-alt" aria-hidden="true"></i> {{ collect([$service->professional->city, $service->professional->state])->filter()->join(', ') ?: 'Cerca de ti' }}</span>
                                    @if ($service->professional->total_reviews > 0)
                                        <span><i class="bi bi-star-fill" aria-hidden="true"></i> {{ number_format((float) $service->professional->average_rating, 1) }} ({{ $service->professional->total_reviews }})</span>
                                    @else
                                        <span>Nuevo · Sin reseñas</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Direct Hire CTAs --}}
                        <div class="d-flex flex-column gap-2 mt-3">
                            <a class="ui-button ui-button--primary ui-button--lg w-100" href="{{ route('job-requests.create', $service) }}">
                                <i class="bi bi-check2-circle" aria-hidden="true"></i> Contratar
                            </a>

                            @auth
                                @if (auth()->user()->isClient())
                                    <form method="POST" action="{{ route('professional.favorite.toggle', $service->professional) }}">
                                        @csrf
                                        <button class="ui-button ui-button--outline w-100" type="submit">
                                            <i class="bi bi-heart{{ $isFavorite ? '-fill' : '' }}" aria-hidden="true"></i> {{ $isFavorite ? 'Quitar profesional de favoritos' : 'Guardar profesional en favoritos' }}
                                        </button>
                                    </form>
                                @endif
                            @else
                                <a class="ui-button ui-button--outline w-100" href="{{ route('login') }}">
                                    <i class="bi bi-heart" aria-hidden="true"></i> Inicia sesión para guardar favoritos
                                </a>
                            @endauth
                        </div>

                        <div class="text-center mt-3">
                            <span class="small text-muted d-inline-flex align-items-center gap-1">
                                <i class="bi bi-lock-fill text-success" aria-hidden="true"></i>
                                Contratación protegida y transparente
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
