@extends('emails.layout')

@section('title', $heading . ' — Chambapp')
@section('hero_icon', '📍')

@section('content')
    <h1 class="email-title">{{ $heading }}</h1>

    <p class="email-lead">{{ $messageText }}</p>

    <table class="info-card" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td colspan="2" class="info-card-header">Actualización de Estado</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">TRABAJO</td>
            <td class="info-value">{{ $job->service?->title ?? $job->title }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">NUEVO ESTADO</td>
            <td class="info-value info-highlight">{{ $statusLabel }}</td>
        </tr>
    </table>

    <div class="btn-container">
        <a href="{{ route('job-requests.show', $job) }}" class="btn-main" target="_blank">Ver detalles de la chamba &rarr;</a>
    </div>
@endsection
