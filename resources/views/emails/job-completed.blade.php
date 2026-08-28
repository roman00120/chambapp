@extends('emails.layout')

@section('title', '¡Chamba completada! — Chambapp')
@section('hero_icon', '⭐')

@section('content')
    <h1 class="email-title">¡Tu chamba ha sido completada!</h1>

    <p class="email-lead">
        Hola <strong>{{ $job->client?->name ?? 'Cliente' }}</strong>,<br />
        El servicio <strong>"{{ $job->service?->title ?? $job->title }}"</strong> ha finalizado con éxito.
    </p>

    <table class="info-card" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td colspan="2" class="info-card-header">Resumen del Servicio</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">TRABAJO</td>
            <td class="info-value">{{ $job->service?->title ?? $job->title }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">PROFESIONAL</td>
            <td class="info-value">{{ $job->professional?->user?->name ?? 'Profesional' }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">ESTADO</td>
            <td class="info-value" style="color: #2d8a62;">Completado con éxito ✓</td>
        </tr>
    </table>

    <p class="email-lead" style="font-size: 14px; margin-bottom: 16px;">
        Tu opinión ayuda a otros clientes a elegir mejor y reconoce el buen trabajo de tu profesional.
    </p>

    <div class="btn-container">
        <a href="{{ route('reviews.create', $job) }}" class="btn-main" target="_blank">Calificar y dejar reseña &rarr;</a>
    </div>
@endsection
