@extends('emails.layout')

@section('title', '¡Cotización aceptada en Chambapp!')
@section('hero_icon', '✓')

@section('content')
    <h1 class="email-title">¡Tu cotización fue aceptada!</h1>

    <p class="email-lead">
        Hola <strong>{{ $quote->professional?->user?->name ?? 'Profesional' }}</strong>,<br />
        El cliente <strong>{{ $quote->jobRequest?->client?->name ?? 'El cliente' }}</strong> ha aceptado tu cotización.
    </p>

    <table class="info-card" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td colspan="2" class="info-card-header">Detalles de la Aceptación</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">TRABAJO</td>
            <td class="info-value">{{ $quote->jobRequest?->service?->title ?? $quote->jobRequest?->title }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">CLIENTE</td>
            <td class="info-value">{{ $quote->jobRequest?->client?->name ?? 'Cliente Chambapp' }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">MONTO COTIZADO</td>
            <td class="info-value info-highlight">${{ number_format((float) $quote->amount, 2) }} MXN</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">ESTADO</td>
            <td class="info-value" style="color: #d97706;">Pendiente de pago por el cliente</td>
        </tr>
    </table>

    <p class="email-lead" style="font-size: 14px; margin-bottom: 16px;">
        El cliente procederá a realizar el pago protegido en custodia. En cuanto se confirme, recibirás el aviso para iniciar el trabajo.
    </p>

    <div class="btn-container">
        <a href="{{ route('job-requests.show', $quote->jobRequest) }}" class="btn-main" target="_blank">Ver estado de la chamba &rarr;</a>
    </div>
@endsection
