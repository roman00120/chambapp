@extends('emails.layout')

@section('title', 'Nueva solicitud de servicio en Chambapp')
@section('hero_icon', '🔔')

@section('content')
    <h1 class="email-title">¡Nueva solicitud de servicio!</h1>

    <p class="email-lead">
        Hola <strong>{{ $job->professional?->user?->name ?? 'Profesional' }}</strong>,<br />
        Un cliente ha seleccionado tu servicio en el catálogo de Chambapp y te ha enviado una solicitud directa para cotizar.
    </p>

    <table class="info-card" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td colspan="2" class="info-card-header">Detalles de la Solicitud</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">SERVICIO</td>
            <td class="info-value info-highlight">{{ $job->service?->title ?? $job->title }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">CLIENTE</td>
            <td class="info-value">{{ $job->client?->name ?? 'Cliente Chambapp' }}</td>
        </tr>
        @if ($job->scheduled_for)
            <tr class="info-row">
                <td class="info-label">FECHA DESEADA</td>
                <td class="info-value">{{ $job->scheduled_for->format('d/m/Y') }}</td>
            </tr>
        @endif
        @if ($job->scheduled_slot)
            <tr class="info-row">
                <td class="info-label">HORARIO</td>
                <td class="info-value">{{ $job->scheduled_slot }}</td>
            </tr>
        @endif
        @if ($job->city)
            <tr class="info-row">
                <td class="info-label">ZONA / CIUDAD</td>
                <td class="info-value">{{ $job->city }}</td>
            </tr>
        @endif
    </table>

    @if ($job->description)
        <div class="callout-box">
            "{{ $job->description }}"
        </div>
    @endif

    <div class="btn-container">
        <a href="{{ route('job-requests.show', $job) }}" class="btn-main" target="_blank">Ver solicitud y cotizar &rarr;</a>
    </div>
@endsection
