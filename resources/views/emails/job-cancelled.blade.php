@extends('emails.layout')

@section('title', 'Chamba cancelada')

@section('content')
    <h1>Aviso de cancelación de chamba</h1>
    <p>Te informamos que la siguiente solicitud de trabajo ha sido cancelada.</p>
    
    <div class="card">
        <div class="card-row">
            <span class="card-label">Chamba:</span>
            <span class="card-value">{{ $job->service?->title ?? $job->title }}</span>
        </div>
        @if($reason)
        <div class="card-row">
            <span class="card-label">Motivo:</span>
            <span class="card-value">{{ $reason }}</span>
        </div>
        @endif
        <div class="card-row">
            <span class="card-label">Estado:</span>
            <span class="card-value" style="color: #dc2626;">Cancelado</span>
        </div>
    </div>

    @if($actionUrl)
    <div class="button-wrapper">
        <a href="{{ $actionUrl }}" class="button">Ver detalles</a>
    </div>
    @endif
@endsection
