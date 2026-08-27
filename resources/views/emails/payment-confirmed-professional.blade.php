@extends('emails.layout')

@section('title', 'El cliente realizó el pago')

@section('content')
    <h1>¡El cliente ha realizado el pago!</h1>
    <p>El cliente <strong>{{ $job->client?->name ?? 'El cliente' }}</strong> ha pagado exitosamente la chamba. El monto está asegurado en custodia.</p>
    
    <div class="card">
        <div class="card-row">
            <span class="card-label">Chamba:</span>
            <span class="card-value">{{ $job->service?->title ?? $job->title }}</span>
        </div>
        <div class="card-row">
            <span class="card-label">Cliente:</span>
            <span class="card-value">{{ $job->client?->name ?? 'Cliente' }}</span>
        </div>
        <div class="card-row">
            <span class="card-label">Monto a recibir:</span>
            <span class="card-value" style="color: #16a34a;">${{ number_format((float) ($payment->professional_net_amount ?? $payment->gross_amount), 2) }} {{ $payment->currency ?? 'MXN' }}</span>
        </div>
        <div class="card-row">
            <span class="card-label">Estado:</span>
            <span class="card-value" style="color: #16a34a;">Pagado / Listo para iniciar</span>
        </div>
    </div>

    <p>Ya puedes ponerte en camino y prestar el servicio. Recuerda marcar tu llegada e inicio desde la aplicación para mantener al cliente informado.</p>

    <div class="button-wrapper">
        <a href="{{ route('job-requests.show', $job) }}" class="button">Iniciar y gestionar chamba</a>
    </div>
@endsection
