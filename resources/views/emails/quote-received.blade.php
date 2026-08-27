@extends('emails.layout')

@section('title', 'Recibiste una nueva cotización — Chambapp')
@section('hero_icon', '📄')

@section('content')
    <h1 class="email-title">¡Recibiste una nueva cotización!</h1>

    <p class="email-lead">
        Hola <strong>{{ $quote->jobRequest?->client?->name ?? 'Cliente' }}</strong>,<br />
        El profesional <strong>{{ $quote->professional?->user?->name ?? 'Un profesional' }}</strong> ha preparado una cotización para tu solicitud.
    </p>

    <table class="info-card" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td colspan="2" class="info-card-header">Resumen de la Propuesta</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">SERVICIO</td>
            <td class="info-value">{{ $quote->jobRequest?->service?->title ?? $quote->jobRequest?->title }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">PROFESIONAL</td>
            <td class="info-value">{{ $quote->professional?->user?->name ?? 'Profesional' }}</td>
        </tr>
        @if ($breakdown)
            <tr class="info-row">
                <td class="info-label">MONTO DEL TRABAJO</td>
                <td class="info-value">${{ number_format((float) $breakdown->baseAmount, 2) }} MXN</td>
            </tr>
            <tr class="info-row">
                <td class="info-label">SERVICIO CHAMBAPP (15%)</td>
                <td class="info-value">${{ number_format((float) $breakdown->clientServiceFee, 2) }} MXN</td>
            </tr>
            <tr class="info-row" style="background-color: #f0fdf4;">
                <td class="info-label" style="font-weight: 700; color: #166534; font-size: 14px;">TOTAL A PAGAR</td>
                <td class="info-value" style="font-size: 16px; color: #15803d; font-weight: 800;">${{ number_format((float) $breakdown->customerTotal, 2) }} MXN</td>
            </tr>
        @else
            <tr class="info-row" style="background-color: #f0fdf4;">
                <td class="info-label" style="font-weight: 700; color: #166534; font-size: 14px;">TOTAL ESTIMADO</td>
                <td class="info-value" style="font-size: 16px; color: #15803d; font-weight: 800;">${{ number_format((float) $quote->amount, 2) }} MXN</td>
            </tr>
        @endif
    </table>

    @if ($quote->notes)
        <div style="background-color: #f8fafc; border-left: 4px solid #0284c7; border-radius: 8px; padding: 14px 18px; margin: 20px 0; text-align: left; font-size: 14px; color: #334155; font-style: italic;">
            "{{ $quote->notes }}"
        </div>
    @endif

    <div class="btn-container">
        <a href="{{ route('job-requests.show', $quote->jobRequest) }}" class="btn-main" target="_blank">Ver y aceptar cotización &rarr;</a>
    </div>
@endsection
