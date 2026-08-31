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

            <x-ui.card padding="lg" class="mt-4" data-identity-verification-status
                data-status-url="{{ route('professional.identity-verification.status') }}"
                data-current-status="{{ $status->value }}"
                data-poll-interval="{{ config('chambapp.identity_verification.polling_interval_seconds', 5) * 1000 }}">
                <h2 class="h4">Estado: <span data-identity-status-label>{{ match ($status->value) { 'not_started' => 'No iniciada', 'pending' => 'Pendiente', 'verified' => 'Verificada', 'rejected' => 'Rechazada', 'needs_review' => 'En revisión', 'expired' => 'Expirada' } }}</span></h2>
                <p class="text-muted">{{ $isRequired ? 'La verificación es obligatoria para operar.' : 'La verificación está disponible, pero todavía no bloquea tu operación.' }}</p>

                @if ($status->value === 'verified')
                    <x-ui.alert variant="success" title="Identidad verificada">El resultado fue confirmado directamente con Didit.</x-ui.alert>
                @elseif (in_array($status->value, ['pending', 'needs_review'], true))
                    <x-ui.alert variant="warning" title="Estamos verificando tu identidad"><span data-identity-status-message>Esperando verificación...</span></x-ui.alert>
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
                <p>Para mayor información sobre el tratamiento de tus datos personales, consulta nuestro <a href="{{ route('legal.privacy') }}" target="_blank" rel="noopener">Aviso de Privacidad</a>.</p>

                @if ($mobileTransferUrl)
                    <section class="identity-mobile-transfer" data-identity-transfer data-transfer-url="{{ $mobileTransferUrl }}">
                        <div>
                            <p class="eyebrow mb-2">Continuar en otro dispositivo</p>
                            <h3 class="h4">Verifica tu identidad desde tu celular</h3>
                            <p>Para verificar tu identidad necesitas un celular con cámara. Escanea el código QR para continuar de forma segura.</p>
                            <p class="small text-muted">Inicia sesión con esta misma cuenta cuando el celular lo solicite. El enlace es temporal, funciona una sola vez y no debes compartirlo.</p>
                            <div class="d-flex flex-wrap gap-2">
                                <button class="ui-button ui-button--primary" type="button" data-copy-transfer-link>Copiar enlace</button>
                                <a class="ui-button ui-button--outline" href="{{ $mobileTransferUrl }}" target="_blank" rel="noopener noreferrer">Abrir verificación aquí</a>
                            </div>
                            <p class="small text-muted mt-3 mb-0" data-copy-transfer-status aria-live="polite">Disponible durante {{ config('chambapp.identity_verification.transfer_ttl_minutes', 10) }} minutos.</p>
                        </div>
                        <div class="identity-mobile-transfer__qr-wrap">
                            <canvas class="identity-mobile-transfer__qr" data-transfer-qr aria-label="Código QR para continuar la verificación en el celular"></canvas>
                            <span>Escanea con la cámara de tu celular</span>
                        </div>
                    </section>
                @endif

                @if ($canStartVerification)
                    <form method="POST" action="{{ route('professional.identity-verification.start') }}">
                        @csrf
                        <label class="d-flex gap-2 align-items-start mb-3">
                            <input type="checkbox" name="identity_consent" value="1" required>
                            <span>Acepto expresamente iniciar la verificación de identidad con Didit para las finalidades descritas.</span>
                        </label>
                        <button class="ui-button ui-button--primary" type="submit">{{ $mobileTransferUrl ? 'Generar un enlace nuevo' : 'Continuar en mi celular' }}</button>
                    </form>
                @endif

                @if (! $canAcceptJobs)
                    <x-ui.alert variant="warning" title="Necesitas verificar tu identidad antes de aceptar trabajos." />
                @endif
            </x-ui.card>
        </div>
    </section>
@endsection
