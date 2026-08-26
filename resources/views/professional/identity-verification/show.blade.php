@extends('layouts.app')

@section('title', 'Verificación de identidad | Chambapp')

@section('content')
    <section class="dashboard-page">
        <div class="container container--narrow">
            <p class="eyebrow">Seguridad profesional</p>
            <h1 class="page-title">Verificación de identidad</h1>
            <p class="page-subtitle">Didit realiza la captura y revisión; Chambapp conserva únicamente el estado y la auditoría mínima.</p>

            @if (session('status'))
                <x-ui.alert variant="success" title="Estado actualizado">{{ session('status') }}</x-ui.alert>
            @endif
            @if ($errors->has('identity_verification'))
                <x-ui.alert variant="danger" title="No se pudo iniciar">{{ $errors->first('identity_verification') }}</x-ui.alert>
            @endif

            <x-ui.card padding="lg" class="mt-4">
                <h2 class="h4">Estado: {{ match ($status->value) { 'not_started' => 'No iniciada', 'pending' => 'Pendiente', 'verified' => 'Verificada', 'rejected' => 'Rechazada', 'needs_review' => 'Requiere revisión', 'expired' => 'Expirada' } }}</h2>
                <p class="text-muted">{{ $isRequired ? 'La verificación es obligatoria para operar.' : 'La verificación está disponible, pero todavía no bloquea tu operación.' }}</p>

                @if ($status->value === 'verified')
                    <x-ui.alert variant="success" title="Identidad verificada">El resultado fue confirmado directamente con Didit.</x-ui.alert>
                @elseif (in_array($status->value, ['pending', 'needs_review'], true))
                    <x-ui.alert variant="warning" title="Estamos verificando tu identidad">Actualizaremos el estado cuando Didit termine su revisión.</x-ui.alert>
                @elseif (! $providerConfigured)
                    <x-ui.alert variant="warning" title="Verificación temporalmente no disponible">La configuración del proveedor está incompleta.</x-ui.alert>
                @endif

                <h3 class="h5 mt-4">Antes de continuar</h3>
                <p>Didit puede tratar tu documento oficial, fotografía o selfie, comparación facial y prueba de vida para verificar identidad, prevenir fraude y proteger la comunidad.</p>
                <ul>
                    <li>La captura ocurre en la sesión segura alojada por Didit.</li>
                    <li>Chambapp no almacena imágenes del documento, selfies, videos ni biometría cruda.</li>
                    <li>Este proceso no equivale a validar el documento contra una base gubernamental específica.</li>
                </ul>
                <p>Consulta el <a href="{{ route('legal.privacy') }}" target="_blank" rel="noopener">Aviso de privacidad</a>. El texto de consentimiento permanece sujeto a revisión jurídica.</p>

                @if ($canStartVerification)
                    <form method="POST" action="{{ route('professional.identity-verification.start') }}">
                        @csrf
                        <label class="d-flex gap-2 align-items-start mb-3">
                            <input type="checkbox" name="identity_consent" value="1" required>
                            <span>Acepto expresamente iniciar la verificación de identidad con Didit para las finalidades descritas.</span>
                        </label>
                        <button class="ui-button ui-button--primary" type="submit">Verificar mi identidad</button>
                    </form>
                @endif

                @if (! $canAcceptJobs)
                    <x-ui.alert variant="warning" title="Necesitas verificar tu identidad antes de aceptar trabajos." />
                @endif
            </x-ui.card>
        </div>
    </section>
@endsection
