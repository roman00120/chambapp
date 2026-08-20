@extends('layouts.app')

@section('title', $jobRequest->title.' | Chambapp')

@php
    $quoteLabels = [
        'pending' => ['Pendiente', 'warning'],
        'accepted' => ['Aceptada', 'success'],
        'rejected' => ['Rechazada', 'danger'],
        'expired' => ['Expirada', 'neutral'],
        'superseded' => ['Reemplazada', 'neutral'],
    ];
    $canCancel = in_array($jobRequest->status, [\App\Enums\JobStatus::PENDING, \App\Enums\JobStatus::ACCEPTED], true);
@endphp

@section('content')
    <section class="job-page">
        <div class="container">
            <div class="job-detail-heading">
                <div>
                    <a class="text-link justify-content-start mb-3" href="{{ $isClient ? route('client.jobs.index') : route('professional.jobs.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Volver</a>
                    <p class="eyebrow mb-2">Detalle del trabajo</p>
                    <h1 class="page-title">{{ $jobRequest->title }}</h1>
                </div>
                <x-job-status-badge :status="$jobRequest->status" />
            </div>

            <div class="row g-4 g-lg-5">
                <div class="col-12 col-lg-7">
                    <x-ui.card padding="lg">
                        <div class="job-detail__service">
                            <span class="service-card__category">{{ $jobRequest->service?->category?->name ?? 'Servicio' }}</span>
                            <h2 class="h4 mt-2 mb-1">{{ $jobRequest->service?->title ?? 'Solicitud personalizada' }}</h2>
                            <p class="text-muted mb-0">{{ $isClient ? 'Profesional: ' : 'Cliente: ' }}<strong>{{ $isClient ? $jobRequest->professional?->user?->name : $jobRequest->client?->name }}</strong></p>
                        </div>
                        <hr>
                        <h2 class="job-section-title">Descripción</h2>
                        <p class="job-description">{{ $jobRequest->description }}</p>
                        <h2 class="job-section-title">Fecha deseada</h2>
                        <p class="job-fact"><i class="bi bi-calendar3" aria-hidden="true"></i> {{ $jobRequest->formattedRequestedDate() }}</p>
                        <h2 class="job-section-title">Ubicación del trabajo</h2>
                        @if ($isClient || $hasApprovedPayment)
                            <div class="job-location"><strong>{{ $jobRequest->address }}</strong><span>{{ $jobRequest->city }}, {{ $jobRequest->state }} · C.P. {{ $jobRequest->postal_code }}</span></div>
                        @else
                            @if (in_array($jobRequest->status, [\App\Enums\JobStatus::MATCHED, \App\Enums\JobStatus::AWAITING_QUOTE], true))<form method="POST" action="{{ route('job-quotes.store', $jobRequest) }}">@csrf<div class="mb-3"><label class="form-label" for="ondemand-quote-amount">Precio propuesto (MXN)</label><input class="form-control" id="ondemand-quote-amount" name="amount" type="number" min="0.01" max="99999999.99" step="0.01" required></div><div class="mb-3"><label class="form-label" for="ondemand-quote-description">Qué incluye</label><textarea class="form-control" id="ondemand-quote-description" name="description" rows="3" maxlength="300" required></textarea></div><x-ui.button class="w-100" type="submit">Enviar cotización</x-ui.button></form>@endif
                            <div class="job-location"><strong>Ubicación aproximada</strong><span>{{ $jobRequest->city }}, {{ $jobRequest->state }} · C.P. {{ $jobRequest->postal_code }}</span><small class="text-muted">La dirección completa se habilitará después del pago.</small></div>
                        @endif
                        <div class="job-price-box mt-4"><span>Precio acordado</span><strong>{{ $jobRequest->formattedAgreedPrice() }}</strong></div>
                        @if ($hasApprovedPayment)
                            <div class="contact-unlocked mt-4"><p class="eyebrow mb-2"><i class="bi bi-unlock" aria-hidden="true"></i> Contratación confirmada</p><h2 class="job-section-title mb-2">Datos para coordinar el servicio</h2><div class="contact-unlocked__grid"><span><small>Teléfono</small><strong>{{ $isClient ? $jobRequest->professional?->user?->phone : $jobRequest->client?->phone }}</strong></span><span><small>Dirección</small><strong>{{ $jobRequest->address }}, {{ $jobRequest->city }}</strong></span></div></div>
                        @else
                            <div class="privacy-note mt-4"><i class="bi bi-shield-lock" aria-hidden="true"></i> Los datos de contacto se habilitan después de la confirmación real del pago.</div>
                        @endif
                    </x-ui.card>

                    <x-ui.card class="mt-4" padding="lg">
                        <div class="d-flex align-items-start justify-content-between gap-3 mb-3"><div><p class="eyebrow mb-2">Historial</p><h2 class="section-title mb-0">Cotizaciones</h2></div><span class="text-muted small">{{ $jobRequest->quotes->count() }} propuestas</span></div>
                        @if ($jobRequest->quotes->isEmpty())
                            <x-ui.empty-state icon="bi-cash-coin" title="{{ $isClient ? 'El profesional aún no ha enviado una cotización.' : 'Envía una cotización para que el cliente pueda contratar el trabajo.' }}" description="Las propuestas se mantienen dentro de Chambapp y no incluyen datos de contacto." />
                        @else
                            <div class="quote-list">
                                @foreach ($jobRequest->quotes->sortByDesc('created_at') as $quote)
                                    @php [$quoteLabel, $quoteVariant] = $quoteLabels[$quote->status->value] ?? ['En revisión', 'neutral']; @endphp
                                    <div class="quote-card {{ $quote->status === \App\Enums\QuoteStatus::ACCEPTED ? 'quote-card--accepted' : '' }}">
                                        <div class="quote-card__header"><div><span class="quote-card__eyebrow">{{ $quote->created_at->locale('es')->translatedFormat('d M Y, g:i a') }}</span><h3 class="quote-card__amount">{{ $quote->formattedAmount() }}</h3></div><x-ui.badge :variant="$quoteVariant" :label="$quoteLabel" /></div>
                                        <p class="quote-card__description">{{ $quote->description }}</p>
                                        @if ($quote->expires_at && $quote->status === \App\Enums\QuoteStatus::PENDING)<p class="quote-card__expiry"><i class="bi bi-clock" aria-hidden="true"></i> Válida hasta {{ $quote->expires_at->locale('es')->translatedFormat('d M Y, g:i a') }}</p>@endif
                                        @if (! $isClient)
                                            @php $quoteMoney = app(\App\Services\PaymentCalculationService::class)->calculate((string) $quote->amount); @endphp
                                            <div class="quote-earnings"><span>Precio <strong>${{ $quoteMoney->grossAmount }} MXN</strong></span><span>Comisión Chambapp ({{ $quoteMoney->platformFeePercent }}%) <strong>-${{ $quoteMoney->platformFee }} MXN</strong></span><span>Monto profesional <strong>${{ $quoteMoney->professionalAmount }} MXN</strong></span></div>
                                            <p class="small text-muted mt-2 mb-0">El procesador de pagos puede aplicar cargos adicionales según las condiciones de tu cuenta.</p>
                                        @endif
                                        @if ($quote->rejection_reason)<p class="quote-card__reason">Motivo: {{ $quote->rejection_reason }}</p>@endif
                                        @if ($isClient && $quote->status === \App\Enums\QuoteStatus::PENDING && ! $quote->isExpired())
                                            <div class="quote-card__actions"><form method="POST" action="{{ route('job-quotes.accept', $quote) }}" data-confirm-form data-confirm-message="Al aceptar, el trabajo quedará pendiente de pago dentro de Chambapp." data-confirm-submit="Aceptar cotización" data-disable-on-submit>@csrf<x-ui.button type="submit"><i class="bi bi-check-lg" aria-hidden="true"></i> Aceptar</x-ui.button></form><form method="POST" action="{{ route('job-quotes.reject', $quote) }}" data-confirm-form data-confirm-message="La cotización será rechazada. El profesional podrá enviar otra propuesta." data-confirm-submit="Rechazar cotización">@csrf<div class="quote-reject-fields"><label class="visually-hidden" for="reject-reason-{{ $quote->id }}">Motivo del rechazo</label><select class="form-select form-select-sm" id="reject-reason-{{ $quote->id }}" name="reason" required><option value="">Motivo</option><option value="price_high">Precio alto</option><option value="changed_need">Cambió mi necesidad</option><option value="no_longer_needed">Ya no necesito el servicio</option><option value="other">Otro</option></select><label class="visually-hidden" for="reject-detail-{{ $quote->id }}">Detalle opcional</label><input class="form-control form-control-sm" id="reject-detail-{{ $quote->id }}" name="reason_detail" maxlength="140" placeholder="Detalle breve (opcional)"></div><x-ui.button variant="danger" type="submit">Rechazar</x-ui.button></form></div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </x-ui.card>
                </div>

                <div class="col-12 col-lg-5">
                    <x-ui.card class="job-sidebar-card mb-4" padding="lg">
                        <h2 class="job-section-title mt-0">Acciones</h2>
                        @if ($errors->any())<x-ui.alert variant="danger">{{ $errors->first() }}</x-ui.alert>@endif
                        @if ($isClient)
                            @if ($jobRequest->status === \App\Enums\JobStatus::AWAITING_PAYMENT)
                                <div class="payment-placeholder"><span class="payment-placeholder__icon"><i class="bi bi-shield-lock" aria-hidden="true"></i></span><h3>Cotización aceptada</h3><p>Total: <strong>{{ $jobRequest->formattedAgreedPrice() }}</strong></p>@if ($canPay)<a class="ui-button ui-button--primary w-100" href="{{ route('client.payments.summary', $jobRequest) }}">Pagar en Chambapp</a>@else<small>El profesional debe conectar Mercado Pago para habilitar el pago.</small>@endif</div>
                            @endif
                            @if ($jobRequest->status === \App\Enums\JobStatus::AWAITING_CONFIRMATION)<form method="POST" action="{{ route('job-requests.complete', $jobRequest) }}" data-confirm-form data-confirm-message="Confirma que el trabajo terminó correctamente." data-confirm-submit="Confirmar finalización">@csrf<x-ui.button class="w-100" type="submit"><i class="bi bi-check2-circle" aria-hidden="true"></i> Confirmar finalización</x-ui.button></form>@endif
                            @if ($canCancel)<form class="mt-2" method="POST" action="{{ route('job-requests.cancel', $jobRequest) }}" data-confirm-form data-confirm-message="El trabajo se cancelará y no podrá continuar con este flujo." data-confirm-submit="Cancelar trabajo">@csrf<div class="mb-2"><label class="visually-hidden" for="cancel-reason-client">Motivo opcional</label><input class="form-control form-control-sm" id="cancel-reason-client" name="cancellation_reason" maxlength="255" placeholder="Motivo opcional"></div><x-ui.button class="w-100" variant="danger" type="submit">Cancelar trabajo</x-ui.button></form>@endif
                        @else
                            @if (in_array($jobRequest->status, [\App\Enums\JobStatus::PENDING, \App\Enums\JobStatus::ACCEPTED], true))<form method="POST" action="{{ route('job-quotes.store', $jobRequest) }}">@csrf<div class="mb-3"><label class="form-label" for="quote-amount">Precio propuesto (MXN)</label><input class="form-control" id="quote-amount" name="amount" type="number" min="0.01" max="99999999.99" step="0.01" value="{{ old('amount') }}" required></div><div class="mb-3"><label class="form-label" for="quote-description">Qué incluye</label><textarea class="form-control" id="quote-description" name="description" rows="3" maxlength="300" required placeholder="Ej. Incluye instalación y materiales básicos.">{{ old('description') }}</textarea><div class="form-text">Máximo 300 caracteres. No compartas datos de contacto.</div></div><x-ui.button class="w-100" type="submit"><i class="bi bi-send" aria-hidden="true"></i> Enviar cotización</x-ui.button></form>@endif
                            @if ($jobRequest->status === \App\Enums\JobStatus::PAID)<form method="POST" action="{{ route('job-requests.on-the-way', $jobRequest) }}">@csrf<x-ui.button class="w-100" type="submit"><i class="bi bi-truck"></i> Avisar que voy en camino</x-ui.button></form>@endif
                            @if ($jobRequest->status === \App\Enums\JobStatus::ON_THE_WAY)<form method="POST" action="{{ route('job-requests.arrive', $jobRequest) }}">@csrf<x-ui.button class="w-100" type="submit"><i class="bi bi-geo-alt"></i> Marcar que llegué</x-ui.button></form>@endif
                            @if ($jobRequest->status === \App\Enums\JobStatus::ARRIVED)<form method="POST" action="{{ route('job-requests.start', $jobRequest) }}">@csrf<x-ui.button class="w-100" type="submit"><i class="bi bi-play-circle" aria-hidden="true"></i> Iniciar trabajo</x-ui.button></form>@endif
                            @if ($jobRequest->status === \App\Enums\JobStatus::IN_PROGRESS)<form method="POST" action="{{ route('job-requests.finish', $jobRequest) }}" data-confirm-form data-confirm-message="El trabajo quedará pendiente de confirmación del cliente." data-confirm-submit="Marcar como terminado">@csrf<x-ui.button class="w-100" type="submit"><i class="bi bi-check2-circle" aria-hidden="true"></i> Marcar como terminado</x-ui.button></form>@endif
                            @if ($canCancel)<form class="mt-2" method="POST" action="{{ route('job-requests.cancel', $jobRequest) }}" data-confirm-form data-confirm-message="El trabajo se cancelará y no podrá continuar con este flujo." data-confirm-submit="Cancelar trabajo">@csrf<div class="mb-2"><label class="visually-hidden" for="cancel-reason-pro">Motivo opcional</label><input class="form-control form-control-sm" id="cancel-reason-pro" name="cancellation_reason" maxlength="255" placeholder="Motivo opcional"></div><x-ui.button class="w-100" variant="danger" type="submit">Cancelar trabajo</x-ui.button></form>@endif
                        @endif
                    </x-ui.card>
                    <x-ui.card padding="lg"><h2 class="job-section-title mt-0">Historial del trabajo</h2><ol class="job-timeline"><li class="is-done"><span class="job-timeline__dot"></span><div><strong>Solicitud enviada</strong><small>{{ $jobRequest->created_at->locale('es')->translatedFormat('d M Y, g:i a') }}</small></div></li>@if ($jobRequest->quotes->isNotEmpty())<li class="is-done"><span class="job-timeline__dot"></span><div><strong>Cotización recibida</strong></div></li>@endif @if ($jobRequest->quotes->contains('status', \App\Enums\QuoteStatus::ACCEPTED))<li class="is-done"><span class="job-timeline__dot"></span><div><strong>Cotización aceptada</strong></div></li><li class="is-done"><span class="job-timeline__dot"></span><div><strong>{{ $hasApprovedPayment ? 'Pago aprobado' : 'Pendiente de pago' }}</strong></div></li>@endif @if ($jobRequest->started_at)<li class="is-done"><span class="job-timeline__dot"></span><div><strong>Trabajo iniciado</strong></div></li>@endif @if ($jobRequest->finished_at)<li class="is-done"><span class="job-timeline__dot"></span><div><strong>Trabajo terminado</strong></div></li>@endif @if ($jobRequest->completed_at)<li class="is-done"><span class="job-timeline__dot"></span><div><strong>Trabajo completado</strong></div></li>@endif</ol></x-ui.card>
                </div>
            </div>
        </div>
    </section>
    @include('jobs._completion-actions')
@endsection
