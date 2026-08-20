@extends('layouts.public')

@section('title', 'Nueva contraseña | Chambapp')

@section('content')
    <section class="auth-page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-5">
                    <div class="auth-card">
                        <p class="eyebrow mb-3">Un último paso</p>
                        <h1 class="auth-title">Crea una contraseña nueva</h1>
                        <p class="auth-copy">Usa una contraseña segura que no hayas utilizado antes.</p>

                        <x-auth.form-errors />

                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <div class="mb-3">
                                <label class="form-label" for="email">Correo electrónico</label>
                                <input class="form-control form-control-lg" id="email" type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="reset-password">Nueva contraseña</label>
                                <div class="password-field">
                                    <input class="form-control form-control-lg" id="reset-password" type="password" name="password" autocomplete="new-password" required>
                                    <button class="password-toggle" type="button" data-password-toggle data-target="#reset-password" aria-controls="reset-password" aria-pressed="false">Mostrar</button>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="reset-password-confirmation">Confirmar contraseña</label>
                                <input class="form-control form-control-lg" id="reset-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                            </div>
                            <button class="btn btn-primary btn-lg w-100" type="submit">Actualizar contraseña</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
