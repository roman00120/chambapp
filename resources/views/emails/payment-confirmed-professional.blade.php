@extends('emails.layout')

@section('title', 'El cliente realizó el pago — Chambapp')
@section('hero_icon', '🚀')

@section('content')
    <h1 class="email-title">¡Pago en custodia confirmado!</h1>

    <p class="email-lead">
        Hola <strong>{{ $job->professional?->user?->name ?? 'Profesional' }}</strong>,<br />
        El cliente <strong>{{ $job->client?->name ?? 'El cliente' }}</strong> ha completado el pago. El dinero se encuentra resguardado en custodia y ya puedes iniciar la chamba.
    </p>

    <table class="info-card" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td colspan="2" class="info-card-header">Detalles de la Chamba</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">TRABAJO</td>
            <td class="info-value">{{ $job->service?->title ?? $job->title }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">CLIENTE</td>
            <td class="info-value">{{ $job->client?->name ?? 'Cliente' }}</td>
        </tr>
        <tr class="info-row" style="background-color: #e3f5e9;">
            <td class="info-label" style="font-weight: 700; color: #17623f;">TU PAGO ASEGURADO</td>
            <td class="info-value" style="font-size: 16px; color: #2d8a62; font-weight: 800;">${{ number_format((float) ($payment->professional_amount ?? $payment->gross_amount), 2) }} MXN</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">ESTADO</td>
            <td class="info-value" style="color: #2d8a62;">Listo para iniciar ✓</td>
        </tr>
    </table>

    <div class="btn-container">
        <a href="{{ route('job-requests.show', $job) }}" class="btn-main" target="_blank">Iniciar y gestionar chamba &rarr;</a>
    </div>
@endsection
