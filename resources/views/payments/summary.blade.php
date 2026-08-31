@extends('layouts.app')

@section('title', 'Confirmar contratación | Chambapp')

@section('content')
<section class="payment-page"><div class="container">
    <div class="page-heading"><div><p class="eyebrow mb-2"><i class="bi bi-shield-lock" aria-hidden="true"></i> Pago seguro</p><h1 class="page-title">Confirmar contratación</h1><p class="section-copy mb-0">Serás redirigido a Mercado Pago para completar el pago. Chambapp no recibe datos de tarjeta.</p></div></div>
    <div class="row g-4 mt-1">
        <div class="col-12 col-lg-7"><x-ui.card padding="lg">
            <p class="eyebrow mb-2">Servicio</p><h2 class="h4 mb-1">{{ $jobRequest->service?->title ?? $jobRequest->title }}</h2><p class="text-muted mb-4">Profesional: <strong>{{ $jobRequest->professional?->user?->name }}</strong></p>
            <div class="payment-summary-list">
                <div><span>Precio base del servicio</span><strong>${{ $calculation->baseAmount }} MXN</strong></div>
                <div><span>Cargo de servicio Chambapp ({{ $calculation->clientServiceFeePercent }}%)</span><strong>${{ $calculation->clientServiceFee }} MXN</strong></div>
                <div><span>Total a pagar</span><strong>${{ $calculation->customerTotal }} MXN</strong></div>
                <div><span>Moneda</span><strong>{{ $calculation->currency }}</strong></div>
            </div>
            @if ($errors->any())<x-ui.alert class="mt-4" variant="danger">{{ $errors->first() }}</x-ui.alert>@endif
            @if ($hasApprovedPayment)
                <x-ui.alert class="mt-4" variant="success">Pago aprobado.</x-ui.alert>
            @elseif ($canCheckout)
                <form class="mt-4" method="POST" action="{{ route('client.payments.checkout', $jobRequest) }}" data-payment-form data-payment-message="Preparando pago...">@csrf<x-ui.button class="w-100" size="lg" type="submit"><i class="bi bi-shield-check" aria-hidden="true"></i> Pagar Chamba</x-ui.button></form>
                <p class="small text-muted mt-3 mb-0">El cargo mostrado es un cargo de servicio de Chambapp, no un impuesto.</p>
            @else
                <x-ui.alert class="mt-4" variant="warning">El profesional debe conectar Mercado Pago para habilitar este pago.</x-ui.alert>
            @endif
        </x-ui.card></div>
        <div class="col-12 col-lg-5"><x-ui.card padding="lg"><h2 class="job-section-title mt-0">Protección de datos</h2><p class="text-muted small mb-0">El pago se procesa en el entorno seguro de Mercado Pago. No guardamos números de tarjeta, CVV ni contraseñas.</p></x-ui.card></div>
    </div>
</div></section>
@endsection
