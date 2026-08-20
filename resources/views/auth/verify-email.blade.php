@extends('layouts.public')

@section('title', 'Verifica tu correo | Chambapp')

@section('content')
    <section class="auth-page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-5">
                    <div class="auth-card text-center">
                        <span class="auth-icon" aria-hidden="true">✉</span>
                        <p class="eyebrow justify-content-center mb-3">Casi terminamos</p>
                        <h1 class="auth-title">Verifica tu correo electrónico</h1>
                        <p class="auth-copy mx-auto">Enviamos un enlace a <strong>{{ auth()->user()->email }}</strong>. Confirma tu correo para mantener tu cuenta protegida.</p>

                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button class="btn btn-primary btn-lg w-100" type="submit">Reenviar correo</button>
                        </form>
                        <a class="btn btn-link mt-2" href="{{ route(auth()->user()->dashboardRoute()) }}">Continuar por ahora</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
