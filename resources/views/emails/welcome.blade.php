@extends('emails.layout')

@section('title', '¡Bienvenido a Chambapp!')
@section('hero_icon', '🎉')

@section('content')
    <h1 class="email-title">¡Bienvenido a Chambapp,<br /><span class="title-accent">{{ $user->name }}</span>! 👋</h1>

    @if ($user->isProfessional())
        <p class="email-lead">
            Tu cuenta profesional ya está lista.<br />
            Ahora puedes comenzar a ofrecer tus servicios, recibir solicitudes de clientes y enviar cotizaciones de manera sencilla y segura.
        </p>

        <table class="info-card" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td colspan="2" class="info-card-header">¿Qué puedes hacer en Chambapp?</td>
            </tr>
            <tr class="feature-item">
                <td class="feature-icon-cell"><span class="feature-icon">👤</span></td>
                <td>Recibir solicitudes directas de clientes</td>
            </tr>
            <tr class="feature-item">
                <td class="feature-icon-cell"><span class="feature-icon">📄</span></td>
                <td>Enviar cotizaciones transparentes</td>
            </tr>
            <tr class="feature-item">
                <td class="feature-icon-cell"><span class="feature-icon">💼</span></td>
                <td>Gestionar el avance de tus chambas</td>
            </tr>
            <tr class="feature-item">
                <td class="feature-icon-cell"><span class="feature-icon">💳</span></td>
                <td>Recibir tus pagos protegidos en custodia</td>
            </tr>
            <tr class="feature-item">
                <td class="feature-icon-cell"><span class="feature-icon">📈</span></td>
                <td>Hacer crecer tu reputación y tu negocio</td>
            </tr>
        </table>

        <div class="btn-container">
            <a href="{{ route('professional.dashboard') }}" class="btn-main" target="_blank">Entrar a mi panel profesional &rarr;</a>
        </div>
    @else
        <p class="email-lead">
            Tu cuenta en Chambapp ya está lista.<br />
            Encuentra y contrata a los mejores profesionales independientes con la máxima seguridad y garantía en cada chamba.
        </p>

        <table class="info-card" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td colspan="2" class="info-card-header">¿Qué puedes hacer en Chambapp?</td>
            </tr>
            <tr class="feature-item">
                <td class="feature-icon-cell"><span class="feature-icon">🔍</span></td>
                <td>Explorar cientos de servicios verificados</td>
            </tr>
            <tr class="feature-item">
                <td class="feature-icon-cell"><span class="feature-icon">📋</span></td>
                <td>Solicitar cotizaciones sin compromiso</td>
            </tr>
            <tr class="feature-item">
                <td class="feature-icon-cell"><span class="feature-icon">🛡️</span></td>
                <td>Pagar de forma 100% protegida en custodia</td>
            </tr>
            <tr class="feature-item">
                <td class="feature-icon-cell"><span class="feature-icon">📍</span></td>
                <td>Seguir el estado de tu servicio en tiempo real</td>
            </tr>
            <tr class="feature-item">
                <td class="feature-icon-cell"><span class="feature-icon">⭐</span></td>
                <td>Calificar la calidad de la atención recibida</td>
            </tr>
        </table>

        <div class="btn-container">
            <a href="{{ route('client.dashboard') }}" class="btn-main" target="_blank">Explorar servicios &rarr;</a>
        </div>
    @endif
@endsection
