@extends('layouts.public')

@section('title', 'Iniciar sesión | Chambapp')

@section('content')
    <section class="auth-page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-5">
                    <div class="auth-card">
                        <p class="eyebrow mb-3">Qué bueno verte</p>
                        <h1 class="auth-title">Inicia sesión</h1>
                        <p class="auth-copy">Continúa con tu cuenta de Chambapp.</p>

                        <x-auth.form-errors />

                        <a class="ui-button ui-button--outline w-100 mb-3" href="{{ route('auth.google.redirect', ['account_type' => 'client']) }}"><i class="bi bi-google" aria-hidden="true"></i> Continuar con Google</a>
                        <div class="auth-divider mb-3"><span>o con tu correo</span></div>

                        <form method="POST" action="{{ route('login.store') }}" novalidate>
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="email">Correo electrónico</label>
                                <input class="form-control form-control-lg @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label mb-0" for="login-password">Contraseña</label>
                                    <a class="small text-link" href="{{ route('password.request') }}">¿La olvidaste?</a>
                                </div>
                                <div class="password-field">
                                    <input class="form-control form-control-lg @error('password') is-invalid @enderror" id="login-password" type="password" name="password" autocomplete="current-password" required>
                                    <button class="password-toggle" type="button" data-password-toggle data-target="#login-password" aria-controls="login-password" aria-pressed="false">Mostrar</button>
                                </div>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" id="remember" type="checkbox" name="remember" value="1">
                                <label class="form-check-label" for="remember">Recordar sesión</label>
                            </div>

                            <button class="btn btn-primary btn-lg w-100" type="submit">Iniciar sesión</button>
                        </form>

                        <p class="auth-footer-copy mb-0">¿Todavía no tienes cuenta? <a class="text-link" href="{{ route('register') }}">Crear cuenta</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
