@extends('emails.layout')

@section('title', 'Chamba completada')

@section('content')
    <h1>¡Tu chamba ha sido completada con éxito!</h1>
    <p>El trabajo ha sido confirmado como terminado. El pago ha sido liberado al profesional.</p>
    
    <div class="card">
        <div class="card-row">
            <span class="card-label">Chamba:</span>
            <span class="card-value">{{ $job->service?->title ?? $job->title }}</span>
        </div>
        <div class="card-row">
            <span class="card-label">Profesional:</span>
            <span class="card-value">{{ $job->professional?->user?->name ?? 'Profesional' }}</span>
        </div>
        <div class="card-row">
            <span class="card-label">Estado:</span>
            <span class="card-value" style="color: #16a34a;">Completado</span>
        </div>
    </div>

    <p>¿Qué te pareció el servicio? Tu opinión es muy valiosa para ayudar a otros clientes y reconocer el buen trabajo de los profesionales en Chambapp.</p>

    <div class="button-wrapper">
        <a href="{{ route('reviews.create', $job) }}" class="button" style="background-color: #16a34a;">Calificar servicio</a>
    </div>
@endsection
