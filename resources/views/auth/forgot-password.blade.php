@extends('layouts.public')

@section('title', 'Recuperar contraseña | Chambapp')

@section('content')
    <section class="auth-page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-5">
                    <div class="auth-card">
                        <p class="eyebrow mb-3">Recupera el acceso</p>
                        <h1 class="auth-title">¿Olvidaste tu contraseña?</h1>
                        <p class="auth-copy">Escribe tu correo y te enviaremos un enlace para crear una contraseña nueva.</p>

                        <x-auth.form-errors />

                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label" for="email">Correo electrónico</label>
                                <input class="form-control form-control-lg" id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                            </div>
                            <button class="btn btn-primary btn-lg w-100" type="submit">Enviar enlace</button>
                        </form>

                        <p class="auth-footer-copy mb-0"><a class="text-link" href="{{ route('login') }}">Volver a iniciar sesión</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
