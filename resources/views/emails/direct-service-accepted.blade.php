@extends('emails.layout')

@section('title', '¡Tu solicitud fue aceptada!')
@section('hero_icon', '✅')

@section('content')
    <h1 class="email-title">¡Tu solicitud fue aceptada!</h1>

    <p class="email-lead">
        Hola <strong>{{ $job->client?->name ?? 'Cliente' }}</strong>,<br />
        <strong>{{ $job->professional?->user?->name ?? 'El profesional' }}</strong> ha aceptado tu solicitud para el servicio <strong>{{ $serviceTitle }}</strong>.
    </p>

    <table class="info-card" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td colspan="2" class="info-card-header">Resumen de Contratación</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">SERVICIO</td>
            <td class="info-value info-highlight">{{ $serviceTitle }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">PROFESIONAL</td>
            <td class="info-value">{{ $job->professional?->user?->name ?? 'Profesional' }}</td>
        </tr>
        @if ($job->agreed_price)
            <tr class="info-row">
                <td class="info-label">PRECIO BASE</td>
                <td class="info-value">${{ number_format((float)$job->agreed_price, 2) }} MXN</td>
            </tr>
        @endif
        @if ($job->customer_total)
            <tr class="info-row">
                <td class="info-label">TOTAL A PAGAR</td>
                <td class="info-value info-highlight">${{ number_format((float)$job->customer_total, 2) }} MXN</td>
            </tr>
        @endif
        @if ($job->scheduled_for)
            <tr class="info-row">
                <td class="info-label">FECHA PROGRAMADA</td>
                <td class="info-value">{{ $job->scheduled_for->format('d/m/Y') }}</td>
            </tr>
        @endif
        @if ($job->scheduled_slot)
            <tr class="info-row">
                <td class="info-label">HORARIO</td>
                <td class="info-value">{{ $job->scheduled_slot }}</td>
            </tr>
        @endif
    </table>

    <div class="callout-box">
        <strong>Paso obligatorio:</strong> Realiza el pago dentro de Chambapp para asegurar tu fecha y formalizar el inicio de la chamba. Tu dinero queda protegido por nuestra garantía hasta que confirmes la entrega del trabajo.
    </div>

    <div class="btn-container">
        <a href="{{ route('job-requests.show', $job) }}" class="btn-main" target="_blank">Pagar Chamba &rarr;</a>
    </div>
@endsection
