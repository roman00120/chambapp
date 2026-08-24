@extends('layouts.app')

@section('title', 'Verificación de identidad | Chambapp')

@section('content')
    <section class="dashboard-page">
        <div class="container container--narrow">
            <p class="eyebrow">Seguridad profesional</p>
            <h1 class="page-title">Verificación de identidad</h1>
            <p class="page-subtitle">Este control será independiente de la revisión de tu perfil, correo y teléfono.</p>

            <x-ui.card padding="lg" class="mt-4">
                <h2 class="h4">Estado: {{ match ($status->value) { 'not_started' => 'No iniciada', 'pending' => 'En revisión', 'verified' => 'Identidad verificada', 'rejected' => 'No aprobada', 'needs_review' => 'Revisión necesaria', 'expired' => 'Vencida' } }}</h2>
                <p class="text-muted">{{ $isRequired ? 'La verificación es obligatoria para operar.' : 'La verificación todavía no es obligatoria mientras seleccionamos e integramos un proveedor especializado.' }}</p>
                @if ($canAcceptJobs)
                    <x-ui.alert variant="success" title="Tu operación actual no está bloqueada.">La función está desactivada de forma segura hasta contar con una verificación real.</x-ui.alert>
                @else
                    <x-ui.alert variant="warning" title="Necesitas verificar tu identidad antes de aceptar trabajos." />
                @endif

                <h3 class="h5 mt-4">Qué se verificará</h3>
                <ul>
                    <li>Documento oficial vigente y autenticidad.</li>
                    <li>Coincidencia facial y prueba de vida, cuando el proveedor lo requiera.</li>
                    <li>Resultado y vigencia de la verificación.</li>
                </ul>
                <p>Chambapp no guarda imágenes de documentos ni biometría en esta arquitectura. Sólo conservará el estado mínimo, referencias técnicas y fechas necesarias. Consulta el <a href="{{ route('legal.privacy') }}">Aviso de privacidad</a>.</p>
                <button class="ui-button ui-button--primary" type="button" disabled aria-disabled="true">Iniciar verificación (próximamente)</button>
            </x-ui.card>
        </div>
    </section>
@endsection
