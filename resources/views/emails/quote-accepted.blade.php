@extends('emails.layout')

@section('title', 'Cotización aceptada')

@section('content')
    <h1>¡Tu cotización fue aceptada!</h1>
    <p>El cliente <strong>{{ $quote->jobRequest?->client?->name ?? 'El cliente' }}</strong> ha aceptado tu propuesta.</p>
    
    <div class="card">
        <div class="card-row">
            <span class="card-label">Chamba:</span>
            <span class="card-value">{{ $quote->jobRequest?->service?->title ?? $quote->jobRequest?->title }}</span>
        </div>
        <div class="card-row">
            <span class="card-label">Monto acordado:</span>
            <span class="card-value">${{ number_format((float) $quote->amount, 2) }} MXN</span>
        </div>
        <div class="card-row">
            <span class="card-label">Estado:</span>
            <span class="card-value" style="color: #d97706;">Pendiente de pago por el cliente</span>
        </div>
    </div>

    <p>El cliente procederá a realizar el pago en custodia a través de Chambapp. Te notificaremos en cuanto el pago sea confirmado para que puedas iniciar el trabajo.</p>

    <div class="button-wrapper">
        <a href="{{ route('job-requests.show', $quote->jobRequest) }}" class="button">Ver estado de la chamba</a>
    </div>
@endsection
