@extends('emails.layout')

@section('title', 'Nueva solicitud de servicio')

@section('content')
    <h1>¡Tienes una nueva solicitud de servicio!</h1>
    <p>Un cliente ha solicitado directamente uno de tus servicios publicados en el catálogo de Chambapp.</p>
    
    <div class="card">
        <div class="card-row">
            <span class="card-label">Servicio:</span>
            <span class="card-value">{{ $job->service?->title ?? $job->title }}</span>
        </div>
        <div class="card-row">
            <span class="card-label">Cliente:</span>
            <span class="card-value">{{ $job->client?->name ?? 'Cliente' }}</span>
        </div>
        @if($job->scheduled_for)
        <div class="card-row">
            <span class="card-label">Fecha programada:</span>
            <span class="card-value">{{ $job->scheduled_for->format('d/m/Y') }}</span>
        </div>
        @endif
        @if($job->scheduled_slot)
        <div class="card-row">
            <span class="card-label">Horario:</span>
            <span class="card-value">{{ $job->scheduled_slot }}</span>
        </div>
        @endif
        @if($job->city)
        <div class="card-row">
            <span class="card-label">Ubicación:</span>
            <span class="card-value">{{ $job->city }}{{ $job->state ? ', '.$job->state : '' }}</span>
        </div>
        @endif
    </div>

    @if($job->description)
    <p><strong>Detalle de la solicitud:</strong><br>{{ $job->description }}</p>
    @endif

    <div class="button-wrapper">
        <a href="{{ route('job-requests.show', $job) }}" class="button">Ver solicitud y cotizar</a>
    </div>
@endsection
