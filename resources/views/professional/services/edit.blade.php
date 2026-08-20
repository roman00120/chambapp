@extends('layouts.app')

@section('title', 'Editar servicio | Chambapp')

@section('content')
    <section class="professional-page">
        <div class="container">
            <div class="page-heading">
                <div>
                    <p class="eyebrow mb-2"><i class="bi bi-pencil-square" aria-hidden="true"></i> Editar servicio</p>
                    <h1 class="page-title">Actualiza tu publicación.</h1>
                    <p class="section-copy mb-0">Los cambios se reflejarán cuando guardes el formulario.</p>
                </div>
                <a class="text-link" href="{{ route('professional.services.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Mis servicios</a>
            </div>

            <div class="row justify-content-center">
                <div class="col-12 col-xl-9">
                    <x-ui.card padding="lg">
                        @include('professional.services._form', ['service' => $service])
                    </x-ui.card>
                </div>
            </div>
        </div>
    </section>
@endsection
