@extends('layouts.public')

@section('title', 'Crear cuenta | Chambapp')

@section('content')
    <section class="auth-page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-9 col-lg-6">
                    <div class="auth-card">
                        <p class="eyebrow mb-3">Empieza con Chambapp</p>
                        <h1 class="auth-title">Crea tu cuenta</h1>
                        <p class="auth-copy">Elige cómo quieres participar en la comunidad.</p>

                        <x-auth.form-errors />

                        <div class="d-flex flex-column flex-sm-row gap-2 mb-3">
                            <a class="ui-button ui-button--outline flex-fill" href="{{ route('auth.google.redirect', ['account_type' => 'client']) }}"><i class="bi bi-google" aria-hidden="true"></i> Registrarme como cliente</a>
                            <a class="ui-button ui-button--outline flex-fill" href="{{ route('auth.google.redirect', ['account_type' => 'professional']) }}"><i class="bi bi-google" aria-hidden="true"></i> Registrarme como profesional</a>
                        </div>
                        <div class="auth-divider mb-3"><span>o con tu correo</span></div>

                        <form method="POST" action="{{ route('register.store') }}" novalidate>
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="name">Nombre completo</label>
                                <input class="form-control form-control-lg" id="name" type="text" name="name" value="{{ old('name') }}" autocomplete="name" required autofocus>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="register-email">Correo electrónico</label>
                                <input class="form-control form-control-lg" id="register-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="phone">Teléfono</label>
                                <input class="form-control form-control-lg" id="phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" inputmode="tel" placeholder="55 1234 5678" required>
                            </div>

                            <fieldset class="mb-4">
                                <legend class="form-label mb-2">¿Qué quieres hacer en Chambapp?</legend>
                                <div class="account-choice-grid">
                                    <div>
                                        <input class="account-choice-input" id="account-client" type="radio" name="account_type" value="client" @checked(old('account_type', 'client') === 'client') required>
                                        <label class="account-choice" for="account-client">
                                            <strong>Cliente</strong>
                                            <span>Busco contratar servicios</span>
                                        </label>
                                    </div>
                                    <div>
                                        <input class="account-choice-input" id="account-professional" type="radio" name="account_type" value="professional" @checked(old('account_type') === 'professional')>
                                        <label class="account-choice" for="account-professional">
                                            <strong>Profesional</strong>
                                            <span>Quiero ofrecer mis servicios</span>
                                        </label>
                                    </div>
                                </div>
                            </fieldset>

                            <div class="mb-3">
                                <label class="form-label" for="register-password">Contraseña</label>
                                <div class="password-field">
                                    <input class="form-control form-control-lg" id="register-password" type="password" name="password" autocomplete="new-password" required>
                                    <button class="password-toggle" type="button" data-password-toggle data-target="#register-password" aria-controls="register-password" aria-pressed="false">Mostrar</button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label" for="password_confirmation">Confirmar contraseña</label>
                                <div class="password-field">
                                    <input class="form-control form-control-lg" id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                                    <button class="password-toggle" type="button" data-password-toggle data-target="#password_confirmation" aria-controls="password_confirmation" aria-pressed="false">Mostrar</button>
                                </div>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" id="terms_accepted" type="checkbox" name="terms_accepted" value="1">
                                <label class="form-check-label small" for="terms_accepted">
                                    Confirmo que revisaré los <a class="text-link" href="{{ route('legal.terms') }}">Términos</a> y el <a class="text-link" href="{{ route('legal.privacy') }}">Aviso de privacidad</a> antes del lanzamiento comercial.
                                </label>
                            </div>

                            <button class="btn btn-primary btn-lg w-100" type="submit">Crear cuenta</button>
                        </form>

                        <p class="auth-footer-copy mb-0">¿Ya tienes cuenta? <a class="text-link" href="{{ route('login') }}">Iniciar sesión</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
