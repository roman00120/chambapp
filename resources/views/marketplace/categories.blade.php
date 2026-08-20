@extends('layouts.public')

@section('title', 'Categorías de servicios | Chambapp')
@section('meta_description', 'Explora categorías de servicios y encuentra profesionales en Chambapp.')

@section('content')
    <section class="marketplace-page">
        <div class="container">
            <div class="marketplace-heading mb-4"><p class="eyebrow mb-2"><i class="bi bi-grid" aria-hidden="true"></i> Explora por categoría</p><h1 class="page-title">Encuentra una categoría para empezar.</h1><p class="section-copy mb-0">Navega por los servicios disponibles en Chambapp.</p></div>
            <div class="row g-3 g-lg-4">
                @forelse ($categories as $category)
                    <div class="col-6 col-md-4 col-lg-3"><a class="marketplace-category-card" href="{{ route('marketplace.category', $category) }}"><span class="marketplace-category-card__icon"><i class="bi bi-{{ $category->icon ?: 'grid' }}" aria-hidden="true"></i></span><h2>{{ $category->name }}</h2><p>{{ $category->description ?: 'Descubre profesionales y servicios de esta categoría.' }}</p><span class="marketplace-category-card__link">Explorar <i class="bi bi-arrow-right" aria-hidden="true"></i></span></a></div>
                @empty
                    <div class="col-12"><x-ui.empty-state icon="bi-grid" title="No hay categorías activas." description="Estamos preparando nuevas opciones para explorar." /></div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
