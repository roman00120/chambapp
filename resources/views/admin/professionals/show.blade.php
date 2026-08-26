@extends('layouts.app')

@section('title', 'Profesional | Administración')

@section('content')
    @php($identity = $professional->identityVerification)
    <section class="admin-page pb-0">
        <div class="container">
            <x-ui.card padding="lg">
                <p class="eyebrow">Verificación de identidad (sólo lectura)</p>
                <h2 class="h5">{{ $identity?->status?->value ?? 'not_started' }}</h2>
                <p class="text-muted mb-2">La habilitación interna del perfil no acredita identidad. Este panel no puede aprobar una identidad manualmente.</p>
                <p class="small mb-1">Proveedor: {{ $identity?->verification_provider ?? 'No configurado' }}</p>
                <p class="small mb-1">Referencia: {{ filled($identity?->provider_session_id) ? \Illuminate\Support\Str::mask($identity->provider_session_id, '*', 4, max(strlen($identity->provider_session_id) - 8, 0)) : 'Sin sesión' }}</p>
                <p class="small mb-1">Estado proveedor: {{ $identity?->provider_status ?? 'Sin dato' }}</p>
                <p class="small mb-1">Inicio: {{ $identity?->started_at?->format('d/m/Y H:i') ?? 'No' }} · Envío: {{ $identity?->submitted_at?->format('d/m/Y H:i') ?? 'No' }}</p>
                <p class="small mb-1">Última sincronización: {{ $identity?->last_provider_sync_at?->format('d/m/Y H:i') ?? 'No' }} · Vence: {{ $identity?->expires_at?->format('d/m/Y') ?? 'Sin dato' }}</p>
                <p class="small mb-3">Consentimientos registrados: {{ $identity?->consents?->count() ?? 0 }}</p>

                @if ($identity?->events?->isNotEmpty())
                    <h3 class="h6 mt-3">Historial KYC reciente</h3>
                    @foreach ($identity->events as $event)
                        <p class="small mb-1">{{ $event->occurred_at?->format('d/m/Y H:i') }} · {{ $event->from_status ?? 'inicio' }} → {{ $event->to_status }} · {{ $event->source }}</p>
                    @endforeach
                @endif

                <h3 class="h6 mt-3">Credenciales profesionales</h3>
                @forelse ($professional->credentials as $credential)
                    <p class="small mb-1">{{ $credential->credential_type }} · {{ $credential->category?->name ?? 'General' }} · {{ $credential->status->value }}</p>
                @empty
                    <p class="small text-muted mb-0">Sin credenciales registradas.</p>
                @endforelse
            </x-ui.card>
        </div>
    </section>

    <section class="admin-page">
        <div class="container">
            <a class="text-link" href="{{ route('admin.professionals.index') }}">← Profesionales</a>
            <div class="row g-4 mt-1">
                <div class="col-12 col-lg-5">
                    <x-ui.card padding="lg">
                        <p class="eyebrow">Perfil #{{ $professional->id }}</p>
                        <h1 class="page-title h3">{{ $professional->user->name }}</h1>
                        <p>{{ $professional->city }}, {{ $professional->state }}</p>
                        <p>Rating: {{ $professional->total_reviews ? number_format((float) $professional->average_rating, 1) : 'Nuevo' }} · {{ $professional->total_reviews }} reseñas · {{ $professional->total_completed_jobs }} completados</p>
                        <p>Mercado Pago: {{ $professional->isMercadoPagoConnected() ? 'Conectado' : 'No conectado' }}</p>
                        <x-ui.badge variant="neutral" :label="$professional->verification_status->value" />
                        <div class="d-flex flex-wrap gap-2 mt-4">
                            @if ($professional->verification_status->value !== 'verified')
                                <form method="POST" action="{{ route('admin.professionals.approve', $professional) }}" data-confirm-form data-confirm-message="Aprueba este perfil como verificado." data-confirm-submit="Aprobar">
                                    @csrf
                                    <button class="ui-button ui-button--primary" type="submit">Aprobar perfil</button>
                                </form>
                            @endif
                            @if ($professional->verification_status->value !== 'rejected')
                                <form method="POST" action="{{ route('admin.professionals.reject', $professional) }}" data-confirm-form data-confirm-message="Rechaza esta verificación." data-confirm-submit="Rechazar">
                                    @csrf
                                    <input class="form-control form-control-sm mb-2" name="reason" maxlength="1000" placeholder="Motivo interno opcional">
                                    <button class="ui-button ui-button--danger" type="submit">Rechazar</button>
                                </form>
                            @endif
                        </div>
                    </x-ui.card>
                </div>
                <div class="col-12 col-lg-7">
                    <x-ui.card class="mb-3" padding="lg">
                        <h2 class="h5">Servicios</h2>
                        @forelse ($professional->services as $service)
                            <a class="admin-mini-link" href="{{ route('admin.services.show', $service) }}">{{ $service->title }} · {{ $service->is_active ? 'Activo' : 'Inactivo' }}</a>
                        @empty
                            <p class="text-muted">Sin servicios.</p>
                        @endforelse
                    </x-ui.card>
                    <x-ui.card padding="lg">
                        <h2 class="h5">Pagos relacionados</h2>
                        @forelse ($professional->payments as $payment)
                            <a class="admin-mini-link" href="{{ route('admin.payments.show', $payment) }}">{{ $payment->external_reference }} · {{ $payment->status->value }} · {{ $payment->gross_amount }} MXN</a>
                        @empty
                            <p class="text-muted">Sin pagos.</p>
                        @endforelse
                    </x-ui.card>
                </div>
            </div>
        </div>
    </section>
@endsection
