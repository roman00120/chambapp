@extends('layouts.app')

@section('title', 'Inicio profesional | Chambapp')

@section('content')
    <section class="dashboard-page">
        <div class="container">
            <div class="dashboard-hero dashboard-hero--professional">
                <div class="d-flex flex-column flex-md-row justify-content-between gap-4">
                    <div>
                        <p class="eyebrow mb-3"><i class="bi bi-person-workspace" aria-hidden="true"></i> Tu espacio profesional</p>
                        <h1 class="dashboard-title">Hola, {{ $user->name }}</h1>
                        <p class="dashboard-copy">Administra tu perfil y tus servicios desde Chambapp.</p>
                    </div>
                    <div class="dashboard-hero__action d-flex flex-column flex-sm-row gap-2"><x-ui.button href="{{ route('professional.jobs.index') }}" variant="secondary"><i class="bi bi-clipboard-check" aria-hidden="true"></i> Ver solicitudes</x-ui.button><x-ui.button href="{{ route('professional.services.create') }}" variant="secondary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Crear servicio</x-ui.button></div>
                </div>
                @unless ($user->hasVerifiedEmail())
                    <div class="verification-note" role="status">
                        <strong><i class="bi bi-shield-check" aria-hidden="true"></i> Verifica tu correo.</strong>
                        <span>Te ayudará a mantener tu cuenta protegida.</span>
                        <a href="{{ route('verification.notice') }}">Ver instrucciones</a>
                    </div>
                @endunless
            </div>

            @if ($profile->completionPercentage() < 100)
                <x-ui.alert variant="warning" title="Completa tu perfil para comenzar a ofrecer servicios." class="mb-4">Agrega tu experiencia, ubicación y descripción para que tu presentación esté lista.</x-ui.alert>
            @endif

            @if ($identityRequired && ! $canAcceptJobs)
                <x-ui.alert variant="warning" title="Necesitas verificar tu identidad antes de aceptar trabajos." class="mb-4">
                    <a href="{{ route('professional.identity-verification.show') }}">Consulta tu estado y los próximos pasos.</a>
                </x-ui.alert>
            @endif

            <div class="row g-3 g-lg-4 mb-4">
                <div class="col-12 col-md-4"><x-ui.card class="dashboard-stat h-100" padding="md"><span class="dashboard-stat__icon"><i class="bi bi-person-check" aria-hidden="true"></i></span><span class="dashboard-stat__value">{{ $profile->completionPercentage() }}%</span><span class="dashboard-stat__label">Perfil completado</span></x-ui.card></div>
                <div class="col-12 col-md-4"><x-ui.card class="dashboard-stat h-100" padding="md"><span class="dashboard-stat__icon"><i class="bi bi-tools" aria-hidden="true"></i></span><span class="dashboard-stat__value">{{ $activeServicesCount }}</span><span class="dashboard-stat__label">Servicios activos</span></x-ui.card></div>
                <div class="col-12 col-md-4"><x-ui.card class="dashboard-stat h-100" padding="md"><span class="dashboard-stat__icon"><i class="bi bi-clipboard-check" aria-hidden="true"></i></span><span class="dashboard-stat__value">{{ $pendingRequestsCount }}</span><span class="dashboard-stat__label">Solicitudes pendientes</span></x-ui.card></div>
                <div class="col-12 col-md-4"><x-ui.card class="dashboard-stat h-100" padding="md"><span class="dashboard-stat__icon"><i class="bi bi-briefcase" aria-hidden="true"></i></span><span class="dashboard-stat__value">{{ $activeJobsCount }}</span><span class="dashboard-stat__label">Trabajos activos</span></x-ui.card></div>
                <div class="col-12 col-md-4"><x-ui.card class="dashboard-stat h-100" padding="md"><span class="dashboard-stat__icon"><i class="bi bi-check2-circle" aria-hidden="true"></i></span><span class="dashboard-stat__value">{{ $completedJobsCount }}</span><span class="dashboard-stat__label">Trabajos completados</span></x-ui.card></div>
                <div class="col-12 col-md-4"><x-ui.card class="dashboard-stat h-100" padding="md"><span class="dashboard-stat__icon"><i class="bi bi-star-fill" aria-hidden="true"></i></span><span class="dashboard-stat__value">{{ $rating }}</span><span class="dashboard-stat__label">Calificación · {{ $totalReviews }} reseñas</span></x-ui.card></div>
            </div>

            <x-ui.card class="mb-4" padding="lg"><div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3"><div><p class="eyebrow mb-2">Resumen económico</p><h2 class="h4 mb-2">Pagos aprobados</h2><p class="text-muted mb-0">{{ $paidJobsCount }} trabajos pagados · Precios base {{ app(\App\Services\PaymentCalculationService::class)->formatAmount((string) ($baseRevenue ?: '0.00')) }} · Comisión profesional {{ app(\App\Services\PaymentCalculationService::class)->formatAmount((string) ($professionalCommissions ?: '0.00')) }} · Antes de costos externos {{ app(\App\Services\PaymentCalculationService::class)->formatAmount((string) ($professionalRevenueBeforeExternalCosts ?: '0.00')) }}</p></div><div class="d-flex flex-column flex-sm-row gap-2"><x-ui.button href="{{ route('professional.earnings') }}" variant="outline">Ver ganancias</x-ui.button><x-ui.button href="{{ route('professional.payments.settings') }}" variant="link">Configurar Mercado Pago</x-ui.button></div></div></x-ui.card>

            <x-ui.card class="mb-4 availability-card" padding="lg"><div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3"><div><p class="eyebrow mb-2">On-demand</p><h2 class="h4 mb-1">Disponibilidad para chambas cercanas</h2><p class="text-muted mb-0">{{ $profile->is_available ? 'Estás visible para solicitudes inmediatas.' : 'Activa esta opción cuando puedas atender una solicitud ahora.' }}</p></div><a class="ui-button ui-button--outline ui-button--sm" href="{{ route('professional.opportunities') }}"><i class="bi bi-radar"></i> Ver chambas</a></div><form class="row g-2 align-items-end mt-2" method="POST" action="{{ route('professional.availability.update') }}" data-geolocation-form>@csrf @method('PUT')<div class="col-12 col-sm-5"><label class="form-label" for="service-radius">Radio de servicio</label><select class="form-select" id="service-radius" name="service_radius_km"><option value="5" @selected($profile->service_radius_km == 5)>5 km</option><option value="10" @selected($profile->service_radius_km == 10)>10 km</option><option value="15" @selected($profile->service_radius_km == 15)>15 km</option><option value="25" @selected($profile->service_radius_km == 25)>25 km</option></select></div><input type="hidden" name="is_available" value="{{ $profile->is_available ? 0 : 1 }}"><input type="hidden" name="latitude" data-latitude value="{{ $profile->last_latitude }}"><input type="hidden" name="longitude" data-longitude value="{{ $profile->last_longitude }}"><div class="col-12 col-sm-7 d-flex flex-wrap gap-2"><button class="ui-button ui-button--outline" type="button" data-geolocate><i class="bi bi-crosshair"></i> Actualizar ubicación</button><button class="ui-button ui-button--primary" type="submit">{{ $profile->is_available ? 'Pausar disponibilidad' : 'Activar disponibilidad' }}</button></div><p class="small text-muted mb-0" data-geolocation-status>Ubicación: {{ $profile->location_updated_at?->diffForHumans() ?? 'aún no compartida' }}</p></form></x-ui.card>

            <div class="row g-3 g-lg-4">
                <div class="col-12 col-lg-7">
                    <x-ui.card class="h-100" padding="lg">
                        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                            <div><p class="eyebrow mb-2">Tu catálogo</p><h2 class="h4 mb-0">Servicios publicados</h2></div>
                            <x-ui.badge variant="neutral" :label="$totalServicesCount.' total'" />
                        </div>
                        <p class="text-muted mb-4">Puedes crear, editar, activar o desactivar tus servicios desde tu catálogo.</p>
                        <div class="d-flex flex-column flex-sm-row gap-2">
                            <x-ui.button href="{{ route('professional.services.index') }}" variant="outline">Administrar servicios <i class="bi bi-arrow-right" aria-hidden="true"></i></x-ui.button>
                            <x-ui.button href="{{ route('professional.profile.show') }}" variant="link">Ver mi perfil</x-ui.button>
                        </div>
                    </x-ui.card>
                </div>
                <div class="col-12 col-lg-5">
                    <x-ui.card class="h-100" padding="lg">
                        <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0">Identidad</h2><x-ui.badge :variant="$profile->hasVerifiedIdentity() ? 'success' : 'neutral'" :label="$profile->hasVerifiedIdentity() ? 'Identidad verificada' : 'Identidad no verificada'" /></div>
                        <p class="text-muted mb-3">La revisión del perfil no equivale a una verificación de identidad.</p>
                        <x-ui.button href="{{ route('professional.identity-verification.show') }}" variant="outline">Ver estado</x-ui.button>
                    </x-ui.card>
                </div>
            </div>
        </div>
    </section>
@endsection
