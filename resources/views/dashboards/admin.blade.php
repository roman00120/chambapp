@extends('layouts.app')

@section('title', 'Administración | Chambapp')

@section('content')
    <section class="dashboard-page">
        <div class="container">
            <div class="dashboard-hero dashboard-hero--admin">
                <p class="eyebrow mb-3"><i class="bi bi-shield-lock" aria-hidden="true"></i> Acceso administrativo</p>
                <h1 class="dashboard-title">Panel de administración de Chambapp</h1>
                <p class="dashboard-copy">Esta vista temporal confirma que tu cuenta tiene acceso administrativo.</p>
            </div>

            <div class="row g-3 g-lg-4">
                <div class="col-12 col-lg-8">
                    <x-ui.card class="h-100" padding="lg">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <span class="placeholder-card__icon"><i class="bi bi-speedometer2" aria-hidden="true"></i></span>
                            <div>
                                <h2 class="h5 mb-2">Panel en preparación</h2>
                                <p class="mb-0 text-muted">Las estadísticas y herramientas de administración se implementarán en una fase posterior.</p>
                            </div>
                        </div>
                        <x-ui.alert variant="info" title="Acceso confirmado">Tu rol tiene permisos administrativos para esta área.</x-ui.alert>
                    </x-ui.card>
                </div>
                <div class="col-12 col-lg-4">
                    <x-ui.card class="h-100" padding="lg">
                        <h2 class="h5 mb-3">Módulos</h2>
                        <div class="d-flex flex-column gap-2">
                            <x-ui.badge variant="neutral" label="Usuarios · Próximamente" />
                            <x-ui.badge variant="neutral" label="Reportes · Próximamente" />
                            <x-ui.badge variant="neutral" label="Métricas · Próximamente" />
                        </div>
                    </x-ui.card>
                </div>
            </div>
        </div>
    </section>
@endsection
