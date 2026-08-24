@extends('layouts.app')
@section('title', 'Payment #'.$payment->id.' | Administración')
@section('content')
<section class="admin-page"><div class="container"><a class="text-link" href="{{ route('admin.payments.index') }}">← Pagos</a><x-ui.card class="mt-3" padding="lg">
    <p class="eyebrow">Payment interno #{{ $payment->id }}</p><h1 class="page-title h3">{{ $payment->status->value }}</h1>
    <dl class="admin-detail-list">
        <div><dt>Referencia externa</dt><dd>{{ $payment->external_reference ?: 'Sin referencia' }}</dd></div><div><dt>ID proveedor</dt><dd>{{ $payment->external_payment_id ?: 'Sin ID' }}</dd></div><div><dt>Trabajo</dt><dd>#{{ $payment->job_request_id }} · {{ $payment->jobRequest?->title }}</dd></div>
        <div><dt>Modelo económico</dt><dd>{{ $payment->economic_model_version ?? 'single_platform_fee_15' }}</dd></div><div><dt>Precio base</dt><dd>{{ $payment->base_amount ?? $payment->gross_amount }} {{ $payment->currency }}</dd></div><div><dt>Cargo cliente</dt><dd>{{ $payment->client_service_fee_percent ?? '0.00' }}% · {{ $payment->client_service_fee ?? '0.00' }}</dd></div>
        <div><dt>Comisión profesional</dt><dd>{{ $payment->professional_commission_percent ?? $payment->platform_fee_percent }}% · {{ $payment->professional_commission ?? $payment->platform_fee }}</dd></div><div><dt>Total cliente</dt><dd>{{ $payment->customer_total ?? $payment->gross_amount }}</dd></div><div><dt>Ingreso bruto plataforma</dt><dd>{{ $payment->platform_gross_fee ?? $payment->platform_fee }}</dd></div>
        <div><dt>Monto profesional antes de costos externos</dt><dd>{{ $payment->professional_amount_before_external_costs ?? $payment->professional_amount }}</dd></div><div><dt>Fee proveedor</dt><dd>{{ $payment->provider_fee ?? 'No informado' }}</dd></div><div><dt>Liquidación informada por proveedor</dt><dd>{{ $payment->professional_amount }}</dd></div><div><dt>Reembolso</dt><dd>{{ $payment->refunded_amount ?? '0.00' }}</dd></div>
    </dl>
    <h2 class="h5 mt-4">Eventos</h2><div class="admin-mini-list">@forelse ($payment->transactions as $transaction)<div><strong>{{ $transaction->event_type }}</strong><span>{{ $transaction->created_at->format('d/m/Y H:i') }} · {{ $transaction->provider_event_id ?: 'interno' }}</span></div>@empty<p class="text-muted">Sin eventos.</p>@endforelse</div><p class="small text-muted mt-3 mb-0">Los payloads completos y credenciales del proveedor no se muestran en el panel.</p>
</x-ui.card></div></section>
@endsection
