@extends('layouts.public')

@section('title', $category->name.' | Chambapp')
@section('meta_description', $category->description ?: 'Servicios de '.$category->name.' en Chambapp.')

@section('content')
    <section class="marketplace-page">
        <div class="container">
            <nav class="breadcrumb marketplace-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Inicio</a><span>/</span><a href="{{ route('marketplace.categories') }}">Categorías</a><span>/</span><strong>{{ $category->name }}</strong></nav>
            <div class="marketplace-heading mb-4"><p class="eyebrow mb-2"><i class="bi bi-bookmark" aria-hidden="true"></i> Categoría</p><h1 class="page-title">{{ $category->name }}</h1><p class="section-copy mb-0">{{ $category->description ?: 'Explora servicios y profesionales disponibles en esta categoría.' }}</p></div>
            <div class="marketplace-toolbar mb-4"><div class="marketplace-toolbar__result">{{ $services->total() }} servicios disponibles</div><a class="ui-button ui-button--outline ui-button--sm" href="{{ route('marketplace.search', ['category' => $category->slug]) }}"><i class="bi bi-sliders" aria-hidden="true"></i> Ajustar filtros</a></div>
            @include('marketplace._results')
        </div>
    </section>
@endsection
