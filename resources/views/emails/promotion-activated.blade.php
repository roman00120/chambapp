@extends('emails.layout')

@section('title', 'Promoción activada')

@section('content')
    <h1>¡Tu servicio ha sido destacado con éxito!</h1>
    <p>Hemos confirmado tu pago de promoción. Tu servicio ahora cuenta con mayor visibilidad en los primeros lugares del catálogo de Chambapp.</p>
    
    <div class="card">
        <div class="card-row">
            <span class="card-label">Servicio promocionado:</span>
            <span class="card-value">{{ $service->title }}</span>
        </div>
        <div class="card-row">
            <span class="card-label">Duración:</span>
            <span class="card-value">{{ $days }} {{ $days === 1 ? 'día (24 horas)' : 'días' }}</span>
        </div>
        @if($service->featured_until)
        <div class="card-row">
            <span class="card-label">Válido hasta:</span>
            <span class="card-value">{{ $service->featured_until->format('d/m/Y H:i') }}</span>
        </div>
        @endif
        <div class="card-row">
            <span class="card-label">Estado:</span>
            <span class="card-value" style="color: #16a34a;">Activo y destacado</span>
        </div>
    </div>

    <div class="button-wrapper">
        <a href="{{ route('marketplace.service', $service) }}" class="button">Ver servicio en catálogo</a>
    </div>
@endsection
