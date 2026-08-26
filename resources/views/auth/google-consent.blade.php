@extends('layouts.public')

@section('title', 'Consentimiento legal | Chambapp')

@section('content')
    <section class="auth-page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <div class="auth-card">
                        <p class="eyebrow mb-3">Antes de continuar con Google</p>
                        <h1 class="auth-title">Revisa los documentos legales</h1>
                        <p class="auth-copy">Crearás una cuenta {{ $accountType === 'professional' ? 'profesional' : 'de cliente' }}.</p>
                        <x-auth.form-errors />
                        <form method="POST" action="{{ route('auth.google.register') }}">
                            @csrf
                            <input type="hidden" name="account_type" value="{{ $accountType }}">
                            @foreach ($documents as $document)
                                <input type="hidden" name="legal_documents[{{ $document['document'] }}]" value="{{ $document['version'] }}">
                            @endforeach
                            <div class="form-check mb-4">
                                <input class="form-check-input" id="google_legal_accepted" type="checkbox" name="legal_accepted" value="1" required>
                                <label class="form-check-label" for="google_legal_accepted">
                                    He leído y acepto
                                    @foreach ($documents as $document)
                                        <a class="text-link" href="{{ $document['url'] }}" target="_blank" rel="noopener">{{ $document['title'] }}</a>{{ $loop->remaining === 1 ? ' y ' : ($loop->last ? '.' : ', ') }}
                                    @endforeach
                                </label>
                            </div>
                            <button class="ui-button ui-button--primary w-100" type="submit">Aceptar y continuar con Google</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
