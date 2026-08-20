@extends('layouts.app')

@section('title', 'Mis favoritos | Chambapp')

@section('content')
    <section class="dashboard-page marketplace-page"><div class="container"><div class="page-heading"><div><p class="eyebrow mb-2"><i class="bi bi-heart-fill" aria-hidden="true"></i> Tu selección</p><h1 class="page-title">Mis favoritos</h1><p class="section-copy mb-0">Guarda profesionales para volver a encontrarlos fácilmente.</p></div><x-ui.button href="{{ route('marketplace.search') }}"><i class="bi bi-search" aria-hidden="true"></i> Explorar servicios</x-ui.button></div><div class="row g-3 g-lg-4">@forelse ($favorites as $favorite)<div class="col-12 col-md-6 col-lg-4"><x-professional-card :professional="$favorite->professional" :is-favorite="true" /></div>@empty<div class="col-12"><x-ui.empty-state icon="bi-heart" title="Todavía no tienes favoritos" description="Explora el marketplace y guarda los profesionales que te interesen." action="Explorar servicios" :action-href="route('marketplace.search')" /></div>@endforelse</div>@if ($favorites->hasPages())<div class="mt-4"><x-pagination :paginator="$favorites" /></div>@endif</div></section>
@endsection
