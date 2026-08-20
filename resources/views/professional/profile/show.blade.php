@extends('layouts.app')

@section('title', 'Mi perfil profesional | Chambapp')

@section('content')
    <section class="professional-page">
        <div class="container">
            <div class="page-heading">
                <div>
                    <p class="eyebrow mb-2"><i class="bi bi-person-badge" aria-hidden="true"></i> Mi perfil profesional</p>
                    <h1 class="page-title">Así te verán en Chambapp.</h1>
                    <p class="section-copy mb-0">Mantén actualizada la información que presentarás a futuros clientes.</p>
                </div>
                <x-ui.button href="{{ route('professional.profile.edit') }}"><i class="bi bi-pencil" aria-hidden="true"></i> Editar perfil</x-ui.button>
            </div>

            <div class="row g-3 g-lg-4">
                <div class="col-12 col-lg-8">
                    <x-ui.card class="profile-overview h-100" padding="lg">
                        <div class="profile-overview__header">
                            <x-ui.avatar :user="$profile->user" :src="$profile->profile_photo" :name="$profile->user->name" size="lg" />
                            <div class="profile-overview__identity">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    <h2 class="h3 mb-0">{{ $profile->user->name }}</h2>
                                    <x-ui.badge :variant="$profile->verificationBadgeVariant()" :label="$profile->verificationLabel()" dot />
                                </div>
                                <p class="profile-overview__location mb-0"><i class="bi bi-geo-alt" aria-hidden="true"></i> {{ collect([$profile->city, $profile->state])->filter()->join(', ') ?: 'Ubicación pendiente' }}</p>
                            </div>
                        </div>

                        <div class="profile-overview__bio">
                            <h3 class="profile-section-title">Sobre mí</h3>
                            <p class="mb-0">{{ $profile->bio ?: 'Agrega una descripción para contarle a tus clientes qué haces y cómo puedes ayudarles.' }}</p>
                        </div>

                        <div class="profile-facts">
                            <div><i class="bi bi-award" aria-hidden="true"></i><strong>{{ $profile->experience_years }} {{ $profile->experience_years === 1 ? 'año' : 'años' }}</strong><span>de experiencia</span></div>
                            <div><i class="bi bi-telephone" aria-hidden="true"></i><strong>{{ $profile->user->phone ?: 'Pendiente' }}</strong><span>teléfono</span></div>
                            <div><i class="bi bi-mailbox" aria-hidden="true"></i><strong>{{ $profile->postal_code ?: 'Pendiente' }}</strong><span>código postal</span></div>
                        </div>
                    </x-ui.card>
                </div>

                <div class="col-12 col-lg-4">
                    <x-ui.card class="profile-progress-card h-100" padding="lg">
                        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                            <h2 class="h5 mb-0">Completitud del perfil</h2>
                            <strong class="profile-progress-card__percentage">{{ $profile->completionPercentage() }}%</strong>
                        </div>
                        <div class="progress mb-3" role="progressbar" aria-label="Completitud del perfil" aria-valuenow="{{ $profile->completionPercentage() }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar" style="width: {{ $profile->completionPercentage() }}%"></div>
                        </div>
                        @if ($profile->isComplete())
                            <x-ui.alert variant="success" title="Perfil completo">Tu información está lista para presentarse.</x-ui.alert>
                        @else
                            <p class="small text-muted mb-3">Completa bio, experiencia, ciudad, estado y teléfono para mejorar tu perfil.</p>
                            <x-ui.button href="{{ route('professional.profile.edit') }}" variant="outline" class="w-100">Completar información</x-ui.button>
                        @endif
                    </x-ui.card>
                </div>

                <div class="col-12">
                    <x-ui.card padding="md">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                            <h2 class="h5 mb-0">Métricas del sistema</h2>
                            <x-ui.badge variant="info" label="Administradas por Chambapp" />
                        </div>
                        <div class="profile-stats">
                            <div><span class="profile-stats__value">{{ number_format((float) $profile->average_rating, 1) }}</span><span><i class="bi bi-star-fill" aria-hidden="true"></i> calificación</span></div>
                            <div><span class="profile-stats__value">{{ $profile->total_reviews }}</span><span><i class="bi bi-chat-square-text" aria-hidden="true"></i> reseñas</span></div>
                            <div><span class="profile-stats__value">{{ $profile->total_completed_jobs }}</span><span><i class="bi bi-check2-circle" aria-hidden="true"></i> trabajos completados</span></div>
                        </div>
                    </x-ui.card>
                </div>
            </div>
        </div>
    </section>
@endsection
