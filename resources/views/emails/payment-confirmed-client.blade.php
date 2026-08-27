@extends('emails.layout')

@section('title', 'Pago confirmado')

@section('content')
    <h1>¡Tu pago ha sido confirmado!</h1>
    <p>Hemos recibido correctamente tu pago. Tu dinero está seguro en custodia de Chambapp y será liberado al profesional una vez que confirmes la finalización del trabajo.</p>
    
    <div class="card">
        <div class="card-row">
            <span class="card-label">Chamba:</span>
            <span class="card-value">{{ $job->service?->title ?? $job->title }}</span>
        </div>
        <div class="card-row">
            <span class="card-label">Profesional:</span>
            <span class="card-value">{{ $job->professional?->user?->name ?? 'Profesional' }}</span>
        </div>
        <div class="card-row">
            <span class="card-label">Total pagado:</span>
            <span class="card-value" style="color: #16a34a;">${{ number_format((float) ($payment->customer_total ?? $payment->gross_amount), 2) }} {{ $payment->currency ?? 'MXN' }}</span>
        </div>
        @if($payment->external_payment_id)
        <div class="card-row">
            <span class="card-label">Referencia de pago:</span>
            <span class="card-value">{{ $payment->external_payment_id }}</span>
        </div>
        @endif
        <div class="card-row">
            <span class="card-label">Fecha:</span>
            <span class="card-value">{{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    <p>El profesional ha sido notificado para que comience a coordinar la prestación del servicio.</p>

    <div class="button-wrapper">
        <a href="{{ route('job-requests.show', $job) }}" class="button">Ver detalles de la chamba</a>
    </div>
@endsection
