@extends('emails.layout')

@section('title', 'Aviso de cancelación — Chambapp')
@section('hero_icon', '❌')

@section('content')
    <h1 class="email-title">Aviso de cancelación</h1>

    <p class="email-lead">
        Te informamos que la siguiente solicitud de servicio ha sido cancelada en la plataforma.
    </p>

    <table class="info-card" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td colspan="2" class="info-card-header">Detalles de la Cancelación</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">TRABAJO</td>
            <td class="info-value">{{ $job->service?->title ?? $job->title }}</td>
        </tr>
        @if ($reason)
            <tr class="info-row">
                <td class="info-label">MOTIVO</td>
                <td class="info-value">{{ $reason }}</td>
            </tr>
        @endif
        <tr class="info-row">
            <td class="info-label">ESTADO</td>
            <td class="info-value" style="color: #ef4444;">Cancelado</td>
        </tr>
    </table>

    <p class="email-lead" style="font-size: 14px; margin-bottom: 16px;">
        Si se realizó algún cobro previo en custodia, el proceso de reembolso se gestionará de acuerdo con los términos y condiciones de la plataforma.
    </p>

    <div class="btn-container">
        <a href="{{ $actionUrl }}" class="btn-main" target="_blank">Revisar en Chambapp &rarr;</a>
    </div>
@endsection
