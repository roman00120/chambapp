@extends('layouts.app')

@section('title', 'Editar perfil profesional | Chambapp')

@section('content')
    <section class="professional-page">
        <div class="container">
            <div class="page-heading">
                <div>
                    <p class="eyebrow mb-2"><i class="bi bi-pencil-square" aria-hidden="true"></i> Mi perfil profesional</p>
                    <h1 class="page-title">Edita tu información.</h1>
                    <p class="section-copy mb-0">Cuéntales a tus futuros clientes qué haces y dónde trabajas.</p>
                </div>
                <a class="text-link" href="{{ route('professional.profile.show') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Volver al perfil</a>
            </div>

            <div class="row justify-content-center">
                <div class="col-12 col-xl-9">
                    <x-ui.card padding="lg">
                        <form method="POST" action="{{ route('professional.profile.update') }}" enctype="multipart/form-data" novalidate>
                            @csrf
                            @method('PUT')
                            <div class="profile-form__photo mb-4">
                                <x-ui.avatar id="profile-avatar-preview" :user="$profile->user" :src="$profile->profile_photo" :name="$profile->user->name" size="lg" />
                                <div>
                                    <label class="form-label" for="profile_photo">Foto de perfil</label>
                                    <input class="form-control @error('profile_photo') is-invalid @enderror" id="profile_photo" name="profile_photo" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-avatar-input>
                                    <div class="form-text">JPG, PNG o WEBP. Máximo 2 MB.</div>
                                    @error('profile_photo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="form-section">
                                <h2 class="form-section__title">Información básica</h2>
                                <div class="row g-3">
                                    <div class="col-12 col-md-6"><x-ui.input name="name" label="Nombre completo" :value="$profile->user->name" autocomplete="name" required /></div>
                                    <div class="col-12 col-md-6"><x-ui.input name="phone" label="Teléfono" type="tel" :value="$profile->user->phone" autocomplete="tel" inputmode="tel" required /></div>
                                    <div class="col-12"><x-ui.textarea name="bio" label="Descripción profesional" :value="$profile->bio" rows="5" placeholder="Cuéntanos sobre tu experiencia, tus fortalezas y el tipo de trabajo que realizas." help="Evita compartir datos sensibles o enlaces externos." /></div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h2 class="form-section__title">Experiencia y ubicación</h2>
                                <div class="row g-3">
                                    <div class="col-12 col-md-4"><x-ui.input name="experience_years" label="Años de experiencia" type="number" :value="$profile->experience_years" min="0" max="60" inputmode="numeric" required /></div>
                                    <div class="col-12 col-md-4"><x-ui.input name="city" label="Ciudad" :value="$profile->city" autocomplete="address-level2" /></div>
                                    <div class="col-12 col-md-4"><x-ui.input name="state" label="Estado" :value="$profile->state" autocomplete="address-level1" /></div>
                                    <div class="col-12 col-md-4"><x-ui.input name="postal_code" label="Código postal" :value="$profile->postal_code" inputmode="numeric" autocomplete="postal-code" /></div>
                                </div>
                            </div>

                            <x-ui.alert variant="info" title="Estado de verificación" class="mb-4">{{ $profile->verificationLabel() }}. Este estado es administrado por Chambapp.</x-ui.alert>

                            <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                                <a class="ui-button ui-button--outline" href="{{ route('professional.profile.show') }}">Cancelar</a>
                                <x-ui.button type="submit"><i class="bi bi-check2" aria-hidden="true"></i> Guardar cambios</x-ui.button>
                            </div>
                        </form>
                    </x-ui.card>
                </div>
            </div>
        </div>
    </section>
@endsection
