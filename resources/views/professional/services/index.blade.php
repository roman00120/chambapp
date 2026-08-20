@extends('layouts.app')

@section('title', 'Mis servicios | Chambapp')

@section('content')
    <section class="professional-page">
        <div class="container">
            <div class="page-heading">
                <div>
                    <p class="eyebrow mb-2"><i class="bi bi-tools" aria-hidden="true"></i> Mi catálogo</p>
                    <h1 class="page-title">Mis servicios.</h1>
                    <p class="section-copy mb-0">Administra lo que ofreces y mantén tu catálogo actualizado.</p>
                </div>
                <x-ui.button href="{{ route('professional.services.create') }}"><i class="bi bi-plus-lg" aria-hidden="true"></i> Crear servicio</x-ui.button>
            </div>

            @if ($profile->completionPercentage() < 100)
                <x-ui.alert variant="warning" title="Completa tu perfil para comenzar a ofrecer servicios." class="mb-4">Agrega tu experiencia, ubicación y descripción para presentar mejor tu trabajo.</x-ui.alert>
            @endif

            @if ($services->count())
                <div class="row g-3 g-lg-4">
                    @foreach ($services as $service)
                        <div class="col-12 col-md-6 col-xl-4">
                            <x-ui.card class="service-management-card h-100 p-0" padding="none">
                                @if ($service->coverImage)
                                    <img class="service-management-card__cover" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($service->coverImage->path) }}" alt="{{ $service->coverImage->alt_text ?: $service->title }}" loading="lazy">
                                @else
                                    <div class="service-management-card__cover service-management-card__cover--empty"><i class="bi bi-image" aria-hidden="true"></i><span>Sin portada</span></div>
                                @endif
                                <div class="p-4 d-flex flex-column h-100">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                        <span class="service-card__category">{{ $service->category->name }}</span>
                                        <div class="d-flex gap-1">
                                            <x-ui.badge :variant="$service->is_active ? 'success' : 'neutral'" :label="$service->is_active ? 'Activo' : 'Inactivo'" dot />
                                            @if ($service->is_featured)<x-ui.badge variant="info" label="Destacado" />@endif
                                        </div>
                                    </div>
                                    <h2 class="service-card__title">{{ $service->title }}</h2>
                                    <p class="service-management-card__description">{{ $service->description }}</p>
                                    <strong class="service-card__price mb-3">{{ $service->formattedPrice() }}</strong>
                                    <div class="d-flex flex-column gap-2 mt-auto">
                                        <x-ui.button href="{{ route('professional.services.edit', $service) }}" variant="outline" class="w-100"><i class="bi bi-pencil" aria-hidden="true"></i> Editar</x-ui.button>
                                        <form method="POST" action="{{ route('professional.services.toggle', $service) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="ui-button ui-button--link w-100" type="submit"><i class="bi bi-power" aria-hidden="true"></i> {{ $service->is_active ? 'Desactivar' : 'Activar' }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('professional.services.destroy', $service) }}" data-confirm-delete-form>
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-link btn-sm text-danger w-100" type="submit"><i class="bi bi-trash3" aria-hidden="true"></i> Eliminar servicio</button>
                                        </form>
                                    </div>
                                </div>
                            </x-ui.card>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">{{ $services->links('components.pagination') }}</div>
            @else
                <x-ui.card padding="none">
                    <x-ui.empty-state icon="bi-briefcase" title="Todavía no has publicado servicios." description="Crea tu primer servicio para comenzar a recibir solicitudes." action="Crear servicio" action-href="{{ route('professional.services.create') }}" />
                </x-ui.card>
            @endif
        </div>
    </section>
@endsection
