@extends('emails.layout')

@section('title', 'Actualización de tu chamba')

@section('content')
    <h1>{{ $heading }}</h1>
    <p>{{ $messageText }}</p>
    
    <div class="card">
        <div class="card-row">
            <span class="card-label">Chamba:</span>
            <span class="card-value">{{ $job->service?->title ?? $job->title }}</span>
        </div>
        <div class="card-row">
            <span class="card-label">Estado actual:</span>
            <span class="card-value">{{ $statusLabel }}</span>
        </div>
    </div>

    <div class="button-wrapper">
        <a href="{{ $actionUrl }}" class="button">Ver estado de la chamba</a>
    </div>
@endsection
