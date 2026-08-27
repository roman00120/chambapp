@extends('layouts.public')

@section('title', (!empty($category) ? $category->name : 'Buscar servicios').' | Chambapp')
@section('meta_description', !empty($category) ? 'Explora servicios de '.$category->name.' en Chambapp.' : 'Busca servicios y profesionales verificados en Chambapp.')

@section('content')
    <section class="marketplace-page">
        <div class="container">
            <div class="marketplace-heading">
                <div>
                    <p class="eyebrow mb-2"><i class="bi bi-compass" aria-hidden="true"></i> Marketplace Chambapp</p>
                    <h1 class="page-title">{{ !empty($category) ? $category->name : 'Encuentra lo que necesitas.' }}</h1>
                    <p class="section-copy mb-0">Explora servicios de profesionales verificados y encuentra una opción para tu próximo proyecto.</p>
                </div>
            </div>

            <form class="marketplace-searchbar mb-4" method="GET" action="{{ route('marketplace.search') }}">
                @if (!empty($category))<input type="hidden" name="category" value="{{ $category->slug }}">@endif
                <i class="bi bi-search" aria-hidden="true"></i>
                <label class="visually-hidden" for="marketplace-query">Buscar servicios</label>
                <input id="marketplace-query" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Busca plomería, pintura, diseño..." maxlength="100">
                <x-ui.button type="submit" size="sm">Buscar</x-ui.button>
            </form>

            <div class="marketplace-toolbar mb-4">
                <button class="ui-button ui-button--outline ui-button--sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#marketplace-filters" aria-controls="marketplace-filters"><i class="bi bi-sliders" aria-hidden="true"></i> Filtros @if (count(array_filter($filters ?? [])) > 1)<span class="filter-count">{{ count(array_filter($filters ?? [])) - (!empty($filters['sort']) ? 1 : 0) }}</span>@endif</button>
                <div class="marketplace-toolbar__result">{{ $services->total() }} {{ $services->total() === 1 ? 'servicio encontrado' : 'servicios encontrados' }}</div>
                <form method="GET" action="{{ route('marketplace.search') }}" class="marketplace-sort-form">
                    @foreach ($filters as $key => $value) @if ($key !== 'sort' && $value !== null && $value !== '')<input type="hidden" name="{{ $key }}" value="{{ is_bool($value) ? (int) $value : $value }}">@endif @endforeach
                    <label class="visually-hidden" for="marketplace-sort">Ordenar resultados</label>
                    <select class="form-select form-select-sm" id="marketplace-sort" name="sort" onchange="this.form.submit()">
                        <option value="relevant" @selected(($filters['sort'] ?? 'relevant') === 'relevant')>Relevancia</option>
                        <option value="rating" @selected(($filters['sort'] ?? '') === 'rating')>Mejor calificados</option>
                        <option value="price_low" @selected(($filters['sort'] ?? '') === 'price_low')>Precio menor</option>
                        <option value="price_high" @selected(($filters['sort'] ?? '') === 'price_high')>Precio mayor</option>
                        <option value="recent" @selected(($filters['sort'] ?? '') === 'recent')>Más recientes</option>
                    </select>
                </form>
            </div>

            <div class="row g-4">
                <div class="col-12 col-lg-3">
                    <div class="offcanvas-lg offcanvas-end marketplace-filters" tabindex="-1" id="marketplace-filters" aria-labelledby="marketplace-filters-title">
                        <div class="offcanvas-header px-0 pt-0"><h2 class="offcanvas-title h5" id="marketplace-filters-title">Filtrar resultados</h2><button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas" aria-label="Cerrar filtros"></button></div>
                        <div class="offcanvas-body p-0">@include('marketplace._filter-form')</div>
                    </div>
                </div>
                <div class="col-12 col-lg-9">
                    @if ($category)<div class="marketplace-context mb-3"><i class="bi bi-bookmark" aria-hidden="true"></i> Mostrando servicios de <strong>{{ $category->name }}</strong></div>@endif
                    @include('marketplace._results')
                </div>
            </div>
        </div>
    </section>
@endsection
