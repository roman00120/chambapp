@extends('emails.layout')

@section('title', 'Nueva cotización recibida')

@section('content')
    <h1>¡Recibiste una nueva cotización!</h1>
    <p>El profesional <strong>{{ $quote->professional?->user?->name ?? 'El profesional' }}</strong> ha enviado una propuesta para tu solicitud.</p>
    
    <div class="card">
        <div class="card-row">
            <span class="card-label">Chamba:</span>
            <span class="card-value">{{ $quote->jobRequest?->service?->title ?? $quote->jobRequest?->title }}</span>
        </div>
        <div class="card-row">
            <span class="card-label">Profesional:</span>
            <span class="card-value">{{ $quote->professional?->user?->name ?? 'Profesional' }}</span>
        </div>
        <div class="card-row">
            <span class="card-label">Monto base del trabajo:</span>
            <span class="card-value">${{ number_format((float) $quote->amount, 2) }} MXN</span>
        </div>
        @if($breakdown)
        <div class="card-row">
            <span class="card-label">Tarifa de servicio Chambapp (15%):</span>
            <span class="card-value">${{ number_format((float) $breakdown->clientServiceFee, 2) }} MXN</span>
        </div>
        <div class="divider"></div>
        <div class="card-row total-row">
            <span class="card-label">Total a pagar:</span>
            <span class="card-value">${{ number_format((float) $breakdown->customerTotal, 2) }} MXN</span>
        </div>
        @endif
    </div>

    @if($quote->description)
    <p><strong>Nota del profesional:</strong><br>{{ $quote->description }}</p>
    @endif

    <div class="button-wrapper">
        <a href="{{ route('job-requests.show', $quote->jobRequest) }}" class="button">Ver y aceptar cotización</a>
    </div>
@endsection
