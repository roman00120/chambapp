@extends('layouts.app')
@section('title', $jobRequest->title.' | Chambapp')
@section('content')
<section class="job-page"><div class="container container--narrow" data-on-demand-status data-poll-url="{{ route('client.ondemand.status', $jobRequest) }}" data-poll-interval="{{ config('chambapp.on_demand.polling_interval_seconds', 4) * 1000 }}">
    <a class="text-link" href="{{ route('client.jobs.index') }}"><i class="bi bi-arrow-left"></i> Mis trabajos</a>
    @if (session('status'))<x-ui.alert class="mt-3" variant="success">{{ session('status') }}</x-ui.alert>@endif
    @if ($errors->any())<x-ui.alert class="mt-3" variant="danger">{{ $errors->first() }}</x-ui.alert>@endif
    <div class="on-demand-hero mt-3"><p class="eyebrow">Chamba inmediata</p><h1 class="page-title">{{ $jobRequest->title }}</h1><p class="text-muted mb-0" data-search-message>{{ $jobRequest->status === \App\Enums\JobStatus::SEARCHING ? 'Estamos buscando profesionales disponibles cerca de ti.' : 'La solicitud está lista para continuar.' }}</p></div>
    <x-ui.card class="mt-4" padding="lg"><div class="search-radar {{ $jobRequest->status === \App\Enums\JobStatus::SEARCHING ? 'is-active' : '' }}" data-search-radar><span></span><span></span><i class="bi bi-geo-alt-fill"></i></div><div class="text-center"><x-job-status-badge :status="$jobRequest->status" /><p class="small text-muted mt-3 mb-0" data-search-radius>Radio actual: {{ $jobRequest->search_radius_km ?: 5 }} km</p></div>
        @if ($jobRequest->status === \App\Enums\JobStatus::SEARCHING)<form class="mt-4" method="POST" action="{{ route('client.ondemand.cancel', $jobRequest) }}">@csrf<x-ui.button class="w-100" variant="outline" type="submit">Cancelar búsqueda</x-ui.button></form>@elseif ($jobRequest->status === \App\Enums\JobStatus::EXPIRED || $jobRequest->status === \App\Enums\JobStatus::CANCELLED)<form class="mt-4" method="POST" action="{{ route('client.ondemand.search-again', $jobRequest) }}">@csrf<x-ui.button class="w-100" type="submit">Buscar otro profesional</x-ui.button></form>@else<a class="ui-button ui-button--primary w-100 mt-4" href="{{ route('job-requests.show', $jobRequest) }}">Ver cotización y detalles</a>@endif
    </x-ui.card>
    <x-ui.card class="mt-4" padding="lg"><p class="eyebrow">Solicitud</p><h2 class="h5">{{ $jobRequest->category?->name ?? 'Servicio' }}</h2><p>{{ $jobRequest->description }}</p><div class="privacy-note"><i class="bi bi-shield-lock"></i> Los datos exactos de contacto solo se habilitan después del pago aprobado.</div></x-ui.card>
</div></section>
@endsection
