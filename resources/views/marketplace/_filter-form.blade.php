@php
    $filters = $filters ?? [];
    $priceType = $filters['price_type'] ?? '';
@endphp

<form method="GET" action="{{ route('marketplace.search') }}" class="marketplace-filter-form">
    <div class="marketplace-filter-form__group">
        <label class="form-label" for="filter-category">Categoría</label>
        <select class="form-select" id="filter-category" name="category">
            <option value="">Todas las categorías</option>
            @foreach ($categories as $filterCategory)
                <option value="{{ $filterCategory->slug }}" @selected(($filters['category'] ?? '') === $filterCategory->slug)>{{ $filterCategory->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="marketplace-filter-form__group">
        <label class="form-label" for="filter-city">Ciudad</label>
        <select class="form-select" id="filter-city" name="city">
            <option value="">Todas las ciudades</option>
            @foreach ($cities ?? [] as $city)
                <option value="{{ $city }}" @selected(($filters['city'] ?? '') === $city)>{{ $city }}</option>
            @endforeach
        </select>
    </div>
    <div class="marketplace-filter-form__group">
        <label class="form-label" for="filter-price-type">Tipo de precio</label>
        <select class="form-select" id="filter-price-type" name="price_type">
            <option value="">Cualquier tipo</option>
            <option value="fixed" @selected($priceType === 'fixed')>Precio fijo</option>
            <option value="starting_at" @selected($priceType === 'starting_at')>Desde</option>
            <option value="quote" @selected($priceType === 'quote')>Cotización</option>
        </select>
    </div>
    <div class="marketplace-filter-form__group">
        <span class="form-label d-block">Rango de precio</span>
        <div class="row g-2">
            <div class="col-6"><label class="visually-hidden" for="filter-min-price">Precio mínimo</label><input class="form-control" id="filter-min-price" name="min_price" type="number" min="0" placeholder="Mínimo" value="{{ $filters['min_price'] ?? '' }}"></div>
            <div class="col-6"><label class="visually-hidden" for="filter-max-price">Precio máximo</label><input class="form-control" id="filter-max-price" name="max_price" type="number" min="0" placeholder="Máximo" value="{{ $filters['max_price'] ?? '' }}"></div>
        </div>
    </div>
    <div class="marketplace-filter-form__group">
        <label class="form-label" for="filter-rating">Calificación mínima</label>
        <select class="form-select" id="filter-rating" name="rating">
            <option value="">Cualquier calificación</option>
            @foreach ([5, 4, 3] as $rating)
                <option value="{{ $rating }}" @selected((string) ($filters['rating'] ?? '') === (string) $rating)>{{ $rating }}+ estrellas</option>
            @endforeach
        </select>
    </div>
    <div class="form-check mb-4">
        <input class="form-check-input" id="filter-verified" name="verified" type="checkbox" value="1" @checked(filter_var($filters['verified'] ?? false, FILTER_VALIDATE_BOOLEAN))>
        <label class="form-check-label" for="filter-verified">Solo verificados</label>
    </div>
    <div class="d-flex flex-column gap-2">
        <x-ui.button type="submit" class="w-100"><i class="bi bi-funnel" aria-hidden="true"></i> Aplicar filtros</x-ui.button>
        <a class="ui-button ui-button--link w-100" href="{{ route('marketplace.search', !empty($category) ? ['category' => $category->slug] : []) }}">Limpiar filtros</a>
    </div>
</form>
