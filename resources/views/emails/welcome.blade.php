@extends('emails.layout')

@section('title', 'Bienvenido a Chambapp')

@section('content')
    <h1>¡Hola, {{ $user->name }}!</h1>
    <p>Te damos la bienvenida a <strong>Chambapp</strong>, la plataforma donde conectamos a clientes que necesitan resolver una tarea con los mejores profesionales independientes.</p>
    
    <div class="card">
        <p style="margin: 0; color: #334155;">
            @if($user->isProfessional())
                Tu perfil profesional está listo. Configura tus servicios, establece tus horarios y comienza a recibir solicitudes de clientes cerca de ti.
            @else
                Tu cuenta de cliente está lista. Explora nuestro catálogo de servicios calificados o solicita una chamba en minutos con la seguridad de pagos en custodia.
            @endif
        </p>
    </div>

    <div class="button-wrapper">
        <a href="{{ route('login') }}" class="button">Entrar a Chambapp</a>
    </div>

    <p style="font-size: 13px; color: #64748b; text-align: center;">Si tienes dudas o necesitas ayuda, estamos para servirte.</p>
@endsection
