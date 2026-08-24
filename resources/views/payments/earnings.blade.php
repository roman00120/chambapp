@extends('layouts.app')

@section('title', 'Ganancias | Chambapp')

@section('content')
<section class="payment-page"><div class="container">
    <div class="page-heading"><div><p class="eyebrow mb-2"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i> Pagos aprobados</p><h1 class="page-title">Ganancias</h1><p class="section-copy mb-0">Resumen de importes correspondientes a trabajos con pago aprobado.</p></div></div>
    <div class="payment-history-list mt-4">
        @forelse ($payments as $payment)
            <article class="payment-history-card payment-history-card--earnings">
                <div><p class="job-card__eyebrow">{{ $payment->jobRequest?->service?->title ?? 'Trabajo' }}</p><h2 class="h5 mb-1">{{ $payment->jobRequest?->title }}</h2><p class="small text-muted mb-0">{{ $payment->paid_at?->locale('es')->translatedFormat('d M Y') }}</p></div>
                <div class="payment-earning-breakdown">
                    <span>Precio base <strong>${{ $payment->base_amount ?? $payment->gross_amount }}</strong></span>
                    <span>Comisión Chambapp ({{ $payment->professional_commission_percent ?? $payment->platform_fee_percent }}%) <strong>-${{ $payment->professional_commission ?? $payment->platform_fee }}</strong></span>
                    <span class="payment-earning-total">Monto antes de costos externos <strong>${{ $payment->professional_amount_before_external_costs ?? $payment->professional_amount }}</strong></span>
                    @if ($payment->provider_fee !== null)<small>Costo Mercado Pago informado: ${{ $payment->provider_fee }}</small>@endif
                </div>
            </article>
        @empty
            <x-ui.empty-state icon="bi-graph-up-arrow" title="Aún no hay ganancias" description="Los pagos aprobados aparecerán aquí." />
        @endforelse
    </div>
    <p class="small text-muted mt-3">Los montos estimados son anteriores a impuestos, retenciones y costos externos aplicables.</p>
    @if ($payments->hasPages())<div class="mt-4"><x-pagination :paginator="$payments" /></div>@endif
</div></section>
@endsection
