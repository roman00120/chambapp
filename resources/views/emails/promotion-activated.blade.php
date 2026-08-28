@extends('emails.layout')

@section('title', '¡Promoción activada! — Chambapp')
@section('hero_icon', '🌟')

@section('content')
    <h1 class="email-title">¡Tu servicio ahora está destacado!</h1>

    <p class="email-lead">
        Hola <strong>{{ $service->professional?->user?->name ?? 'Profesional' }}</strong>,<br />
        Tu promoción ha sido activada. Tu servicio cuenta con máxima visibilidad prioritaria en el catálogo para atraer más clientes.
    </p>

    <table class="info-card" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td colspan="2" class="info-card-header">Detalles de la Promoción</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">SERVICIO</td>
            <td class="info-value info-highlight">{{ $service->title }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">CATEGORÍA</td>
            <td class="info-value">{{ $service->category?->name ?? 'Servicios' }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">DURACIÓN</td>
            <td class="info-value">{{ $days }} días de visibilidad</td>
        </tr>
        @if ($service->featured_until)
            <tr class="info-row">
                <td class="info-label">VIGENTE HASTA</td>
                <td class="info-value">{{ $service->featured_until->format('d/m/Y') }}</td>
            </tr>
        @endif
        <tr class="info-row">
            <td class="info-label">ESTADO</td>
            <td class="info-value" style="color: #2d8a62;">Activo y destacado ✓</td>
        </tr>
    </table>

    <div class="btn-container">
        <a href="{{ route('marketplace.service', $service) }}" class="btn-main" target="_blank">Ver mi servicio en catálogo &rarr;</a>
    </div>
@endsection
