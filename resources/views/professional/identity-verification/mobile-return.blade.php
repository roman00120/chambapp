@extends('layouts.public')

@section('title', 'Verificación enviada | Chambapp')

@section('content')
    <section class="py-5">
        <div class="container container--narrow text-center">
            <x-ui.card padding="lg">
                <p class="eyebrow">Verificación de identidad</p>
                <h1 class="page-title h3">Recibimos tu verificación</h1>
                <p>Didit está procesando el resultado. Regresa a Chambapp para consultar el estado confirmado por nuestro servidor.</p>
                <a class="ui-button ui-button--primary" href="chambapp:///professional/identity-verification">Volver a la aplicación</a>
                <p class="small text-muted mt-3 mb-0">Esta página no aprueba ni cambia tu estado de identidad.</p>
            </x-ui.card>
        </div>
    </section>
@endsection
