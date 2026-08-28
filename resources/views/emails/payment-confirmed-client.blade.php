@extends('emails.layout')

@section('title', 'Pago confirmado — Chambapp')
@section('hero_icon', '💳')

@section('content')
    <h1 class="email-title">¡Pago confirmado exitosamente!</h1>

    <p class="email-lead">
        Hola <strong>{{ $job->client?->name ?? 'Cliente' }}</strong>,<br />
        Tu pago ha sido procesado de forma segura. Los fondos se mantienen protegidos en custodia por Chambapp hasta que el servicio sea completado a tu entera satisfacción.
    </p>

    <table class="info-card" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td colspan="2" class="info-card-header">Comprobante de Pago en Custodia</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">TRABAJO</td>
            <td class="info-value">{{ $job->service?->title ?? $job->title }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">PROFESIONAL</td>
            <td class="info-value">{{ $job->professional?->user?->name ?? 'Profesional asignado' }}</td>
        </tr>
        <tr class="info-row" style="background-color: #e3f5e9;">
            <td class="info-label" style="font-weight: 700; color: #17623f;">MONTO TOTAL</td>
            <td class="info-value" style="font-size: 16px; color: #2d8a62; font-weight: 800;">${{ number_format((float) ($payment->customer_total ?? $payment->gross_amount), 2) }} MXN</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">ESTADO</td>
            <td class="info-value" style="color: #2d8a62;">Pago protegido en custodia ✓</td>
        </tr>
    </table>

    <div class="btn-container">
        <a href="{{ route('job-requests.show', $job) }}" class="btn-main" target="_blank">Ver detalle del pago &rarr;</a>
    </div>
@endsection
