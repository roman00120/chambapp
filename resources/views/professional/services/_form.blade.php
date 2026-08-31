@php
    $service = isset($service) ? $service : null;
    $editing = $service !== null;
    $formAction = $editing ? route('professional.services.update', $service) : route('professional.services.store');
    $selectedPriceType = old('price_type', $service?->price_type?->value ?? 'quote');
@endphp

<form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" novalidate>
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div class="form-section">
        <p class="eyebrow mb-2">Paso 1</p>
        <h2 class="form-section__title">Cuéntanos sobre tu servicio</h2>
        <div class="row g-3">
            <div class="col-12"><x-ui.input name="title" label="Título del servicio" :value="$service?->title" placeholder="Ej. Instalación de boiler residencial" required help="Usa un título claro para que tus clientes entiendan qué ofreces." /></div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="category_id">Categoría</label>
                <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                    <option value="">Selecciona una categoría</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('category_id', $service?->category_id) === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-12"><x-ui.textarea name="description" label="Descripción" :value="$service?->description" rows="6" placeholder="Describe qué incluye el servicio, cómo trabajas y qué puede esperar el cliente." required /></div>
        </div>
    </div>

    <div class="form-section">
        <p class="eyebrow mb-2">Paso 2</p>
        <h2 class="form-section__title">Define tu precio</h2>
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label" for="price_type">Tipo de precio</label>
                <select class="form-select @error('price_type') is-invalid @enderror" id="price_type" name="price_type" required data-price-type>
                    @foreach ($priceTypes as $priceType)
                        <option value="{{ $priceType->value }}" @selected($selectedPriceType === $priceType->value)>
                            {{ match ($priceType->value) { 'fixed' => 'Precio fijo', 'starting_at' => 'Desde', 'quote' => 'Cotización' } }}
                        </option>
                    @endforeach
                </select>
                @error('price_type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6" data-price-wrapper>
                <x-ui.input name="price" label="Precio en MXN" type="number" :value="$service?->price" min="0" max="99999999.99" step="0.01" inputmode="decimal" placeholder="650.00" data-price-input />
            </div>
        </div>
    </div>

    <div class="form-section">
        <p class="eyebrow mb-2">Paso 3</p>
        <h2 class="form-section__title">Agrega imágenes</h2>
        <p class="small text-muted">Puedes cargar hasta 5 imágenes JPG, PNG o WEBP de máximo 4 MB cada una. La primera será portada si no eliges otra.</p>
        @if ($editing && $service->images->isNotEmpty())
            <div class="service-image-grid mb-3">
                @foreach ($service->images as $image)
                    <div class="service-image-item">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->path) }}" alt="{{ $image->alt_text ?: $service->title }}" loading="lazy">
                        <div class="service-image-item__actions">
                            <label class="form-check m-0"><input class="form-check-input" type="radio" name="cover_image_id" value="{{ $image->id }}" @checked($image->is_cover)> <span>Portada</span></label>
                            <button class="btn btn-sm btn-outline-danger" type="submit" form="delete-image-{{ $image->id }}" aria-label="Eliminar imagen de {{ $service->title }}">Eliminar</button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
        <label class="form-label" for="images">Nuevas imágenes</label>
        <input class="form-control @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror" id="images" name="images[]" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple data-service-images data-existing-count="{{ $editing ? $service->images->count() : 0 }}">
        <div class="form-text">Selecciona una portada en la vista previa cuando uses un dispositivo compatible.</div>
        @error('images')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        @error('images.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        <div class="service-upload-preview mt-3" data-service-preview></div>
    </div>

    <x-ui.alert variant="info" title="Revisión antes de publicar" class="mb-4">El servicio quedará activo y podrás desactivarlo desde tu lista. La promoción destacada solo puede gestionarse administrativamente.</x-ui.alert>

    <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
        <a class="ui-button ui-button--outline" href="{{ route('professional.services.index') }}">Cancelar</a>
        <x-ui.button type="submit"><i class="bi bi-{{ $editing ? 'check2' : 'rocket-takeoff' }}" aria-hidden="true"></i> {{ $editing ? 'Guardar servicio' : 'Publicar servicio' }}</x-ui.button>
    </div>
</form>

@if ($editing && $service->images->isNotEmpty())
    @foreach ($service->images as $image)
        <form id="delete-image-{{ $image->id }}" method="POST" action="{{ route('professional.service-images.destroy', $image) }}" data-confirm-delete-form class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endif
