@extends('layouts.app')

@section('title', 'Inicio cliente | Chambapp')

@section('content')
    <section class="dashboard-page">
        <div class="container">
            <div class="dashboard-hero dashboard-hero--client">
                <p class="eyebrow mb-3"><i class="bi bi-house-heart" aria-hidden="true"></i> Tu espacio en Chambapp</p>
                <h1 class="dashboard-title">Hola, {{ $user->name }}</h1>
                <p class="dashboard-copy">Encuentra profesionales verificados para lo que necesites.</p>
                @unless ($user->hasVerifiedEmail())<div class="verification-note" role="status"><strong><i class="bi bi-shield-check" aria-hidden="true"></i> Verifica tu correo.</strong><span>Te ayudará a mantener tu cuenta protegida.</span><a href="{{ route('verification.notice') }}">Ver instrucciones</a></div>@endunless
            </div>

            <div class="row g-3 g-lg-4 mb-4">
                <div class="col-12 col-md-4"><x-ui.card class="dashboard-stat h-100" padding="md"><span class="dashboard-stat__icon"><i class="bi bi-hourglass-split" aria-hidden="true"></i></span><span class="dashboard-stat__value">{{ $pendingJobsCount }}</span><span class="dashboard-stat__label">Solicitudes pendientes</span></x-ui.card></div>
                <div class="col-12 col-md-4"><x-ui.card class="dashboard-stat h-100" padding="md"><span class="dashboard-stat__icon"><i class="bi bi-briefcase" aria-hidden="true"></i></span><span class="dashboard-stat__value">{{ $activeJobsCount }}</span><span class="dashboard-stat__label">Trabajos activos</span></x-ui.card></div>
                <div class="col-12 col-md-4"><x-ui.card class="dashboard-stat h-100" padding="md"><span class="dashboard-stat__icon"><i class="bi bi-star-fill" aria-hidden="true"></i></span><span class="dashboard-stat__value">{{ $pendingReviewsCount }}</span><span class="dashboard-stat__label">Trabajos por calificar</span></x-ui.card></div>
            </div>

            <div class="row g-3 g-lg-4 mb-4"><div class="col-12 col-md-6"><x-ui.card class="h-100" padding="lg"><p class="eyebrow">Ahora</p><h2 class="h4">¿Necesitas ayuda ya?</h2><p class="text-muted">Activa una búsqueda por cercanía y recibe una cotización dentro de Chambapp.</p><x-ui.button href="{{ route('client.ondemand.create') }}"><i class="bi bi-lightning-charge"></i> Buscar profesional ahora</x-ui.button></x-ui.card></div><div class="col-12 col-md-6"><x-ui.card class="h-100" padding="lg"><p class="eyebrow">Con calma</p><h2 class="h4">Programa tu servicio</h2><p class="text-muted">Elige fecha y bloque horario para una solicitud planificada.</p><x-ui.button href="{{ route('client.scheduled.create') }}" variant="outline"><i class="bi bi-calendar3"></i> Programar</x-ui.button></x-ui.card></div></div>

            <div class="row g-3 g-lg-4 mb-5">
                <div class="col-12 col-lg-7"><x-ui.card class="h-100" padding="lg"><div class="d-flex align-items-start gap-3 mb-3"><span class="placeholder-card__icon"><i class="bi bi-search" aria-hidden="true"></i></span><div><h2 class="h5 mb-2">Encuentra ayuda cerca de ti</h2><p class="mb-0 text-muted">Busca por servicio, categoría, ciudad o rango de precio.</p></div></div><form class="visual-search mt-4" method="GET" action="{{ route('marketplace.search') }}"><label class="visually-hidden" for="client-search">Buscar servicios</label><input id="client-search" name="q" type="search" placeholder="¿Qué necesitas resolver?"><x-ui.button type="submit" size="sm">Buscar <i class="bi bi-arrow-right" aria-hidden="true"></i></x-ui.button></form></x-ui.card></div>
                <div class="col-12 col-lg-5"><x-ui.card class="h-100" padding="lg"><div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0">Accesos rápidos</h2><x-ui.badge variant="neutral" label="Fase 10" /></div><div class="d-flex flex-column gap-2"><x-ui.button href="{{ route('client.jobs.index') }}" variant="outline"><i class="bi bi-briefcase" aria-hidden="true"></i> Ver mis trabajos</x-ui.button><x-ui.button href="{{ route('client.favorites.index') }}" variant="link"><i class="bi bi-heart" aria-hidden="true"></i> Ver favoritos</x-ui.button></div></x-ui.card></div>
            </div>

            <div class="d-flex align-items-end justify-content-between gap-3 mb-3"><div><p class="eyebrow mb-2">Actividad reciente</p><h2 class="section-title mb-0">Tus últimos trabajos</h2></div><a class="text-link" href="{{ route('client.jobs.index') }}">Ver todos <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>
            <div class="job-list mb-5">
                @forelse ($recentJobs as $jobRequest)
                    <article class="job-card"><div class="job-card__header"><div><p class="job-card__eyebrow">{{ $jobRequest->service?->title ?? 'Solicitud de trabajo' }}</p><h3 class="job-card__title"><a href="{{ route('job-requests.show', $jobRequest) }}">{{ $jobRequest->title }}</a></h3></div><x-job-status-badge :status="$jobRequest->status" audience="client" /></div><div class="job-card__meta"><span><i class="bi bi-person" aria-hidden="true"></i> {{ $jobRequest->professional?->user?->name }}</span><span><i class="bi bi-calendar3" aria-hidden="true"></i> {{ $jobRequest->formattedRequestedDate() }}</span></div><div class="job-card__footer">@if ($jobRequest->status?->value === 'completed' && ! $jobRequest->review)<a class="ui-button ui-button--primary ui-button--sm" href="{{ route('reviews.create', $jobRequest) }}"><i class="bi bi-star" aria-hidden="true"></i> Calificar</a>@elseif ($jobRequest->review)<span class="text-muted small"><i class="bi bi-check2-circle" aria-hidden="true"></i> Calificado</span>@else<span></span>@endif<a class="ui-button ui-button--outline ui-button--sm" href="{{ route('job-requests.show', $jobRequest) }}">Ver detalle</a></div></article>
                @empty
                    <x-ui.empty-state icon="bi-briefcase" title="Aún no tienes trabajos." description="Busca un servicio y envía tu primera solicitud." action="Buscar servicios" :action-href="route('marketplace.search')" />
                @endforelse
            </div>
        </div>
    </section>
@endsection
