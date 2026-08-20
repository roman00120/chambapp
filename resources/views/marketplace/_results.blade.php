@if ($services->count())
    <div class="row g-3 g-lg-4">
        @foreach ($services as $service)
            <div class="col-12 col-md-6 col-xl-4"><x-service-card :service="$service" :is-favorite="in_array($service->professional_id, $favoriteProfessionalIds ?? [], true)" /></div>
        @endforeach
    </div>
    <div class="mt-4">{{ $services->links('components.pagination') }}</div>
@else
    <x-ui.card padding="none"><x-ui.empty-state icon="bi-search" title="No encontramos servicios para tu búsqueda." description="Prueba con otros términos o limpia los filtros para explorar todo el catálogo." action="Explorar categorías" action-href="{{ route('marketplace.categories') }}" /></x-ui.card>
@endif
