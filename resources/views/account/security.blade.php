@extends('layouts.app')

@section('title', 'Seguridad y Reportes | Chambapp')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="bi bi-shield-check text-primary"></i> Seguridad y Reportes</h1>
            <p class="text-muted mb-0">Gestiona tus reportes, revisa el estado de tu cuenta y consulta avisos disciplinarios.</p>
        </div>
        <a href="{{ route('reports.create') }}" class="btn btn-outline-danger">
            <i class="bi bi-flag"></i> Reportar un problema
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-12 col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-2">Estado de la cuenta</h6>
                    <div class="d-flex align-items-center">
                        @if ($user->isBanned())
                            <span class="badge bg-danger fs-6"><i class="bi bi-x-circle"></i> Bloqueada</span>
                        @elseif ($user->isSuspended())
                            <span class="badge bg-warning text-dark fs-6"><i class="bi bi-pause-circle"></i> Suspendida</span>
                        @else
                            <span class="badge bg-success fs-6"><i class="bi bi-check-circle"></i> Activa</span>
                        @endif
                    </div>
                    <small class="text-muted d-block mt-2">Cumples con los lineamientos de la comunidad Chambapp.</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-2">Tarjetas amarillas activas</h6>
                    <div class="d-flex align-items-baseline">
                        <span class="display-6 fw-bold {{ $activeYellowCards > 0 ? 'text-warning' : 'text-success' }}">{{ $activeYellowCards }}</span>
                        <span class="text-muted ms-2">/ 3 límite de advertencias</span>
                    </div>
                    <small class="text-muted d-block mt-2">1: Advertencia · 2: Advertencia grave · 3: Revisión obligatoria.</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-2">Protección y Privacidad</h6>
                    <p class="small text-muted mb-0">Tus reportes y notificaciones disciplinarias son estrictamente privados y no se muestran en perfiles públicos ni búsquedas.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Advertencias / Sanciones Propias -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0"><i class="bi bi-exclamation-triangle text-warning"></i> Mis Avisos y Sanciones</h5>
        </div>
        <div class="card-body p-0">
            @if ($disciplinaryActions->isEmpty())
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-shield-check fs-1 text-success d-block mb-2"></i>
                    No tienes advertencias ni sanciones registradas. ¡Gracias por mantener una conducta ejemplar!
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tipo</th>
                                <th>Motivo</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($disciplinaryActions as $action)
                                <tr>
                                    <td>
                                        @if ($action->action_type->value === 'yellow_card')
                                            <span class="badge bg-warning text-dark"><i class="bi bi-card-text"></i> Tarjeta Amarilla</span>
                                        @elseif ($action->action_type->value === 'warning')
                                            <span class="badge bg-info text-dark"><i class="bi bi-info-circle"></i> Advertencia</span>
                                        @elseif (str_contains($action->action_type->value, 'suspension'))
                                            <span class="badge bg-danger"><i class="bi bi-pause-fill"></i> Suspensión</span>
                                        @else
                                            <span class="badge bg-dark">{{ $action->action_type->label() }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <p class="mb-0 text-dark">{{ $action->reason_text }}</p>
                                        @if ($action->expires_at)
                                            <small class="text-muted">Vigente hasta: {{ $action->expires_at->format('d/m/Y') }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $action->issued_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if ($action->status->value === 'active')
                                            <span class="badge bg-danger">Activa</span>
                                        @elseif ($action->status->value === 'revoked')
                                            <span class="badge bg-success">Revocada</span>
                                        @else
                                            <span class="badge bg-secondary">Expirada</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($action->isActive())
                                            @if ($action->latestAppeal)
                                                <span class="badge bg-light text-dark border">
                                                    Apelación: {{ $action->latestAppeal->status->label() }}
                                                </span>
                                            @else
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#appealModal{{ $action->id }}">
                                                    <i class="bi bi-arrow-return-left"></i> Apelar
                                                </button>

                                                <!-- Modal Apelación -->
                                                <div class="modal fade" id="appealModal{{ $action->id }}" tabindex="-1" aria-labelledby="appealModalLabel{{ $action->id }}" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form method="POST" action="{{ route('disciplinary.appeal', $action) }}">
                                                                @csrf
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="appealModalLabel{{ $action->id }}">Apelar decisión disciplinaria</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p class="small text-muted">Explica de forma clara y respetuosa los motivos por los cuales consideras que esta sanción debe ser revisada y revocada.</p>
                                                                    <div class="mb-3">
                                                                        <label for="appeal_text_{{ $action->id }}" class="form-label fw-bold">Explicación y descargos:</label>
                                                                        <textarea name="appeal_text" id="appeal_text_{{ $action->id }}" class="form-control" rows="4" minlength="20" required placeholder="Escribe aquí tus argumentos..."></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                    <button type="submit" class="btn btn-primary">Enviar apelación</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $disciplinaryActions->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Sección de Mis Reportes Enviados -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0"><i class="bi bi-send text-primary"></i> Mis Reportes Enviados</h5>
        </div>
        <div class="card-body p-0">
            @if ($submittedReports->isEmpty())
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-chat-left-dots fs-1 d-block mb-2"></i>
                    No has enviado reportes.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Referencia</th>
                                <th>Usuario reportado</th>
                                <th>Motivo</th>
                                <th>Fecha de envío</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($submittedReports as $report)
                                <tr>
                                    <td><code>#REP-{{ $report->id }}</code></td>
                                    <td>{{ $report->reported?->name ?? 'Usuario de Chambapp' }}</td>
                                    <td>{{ $report->category->label() }}</td>
                                    <td>{{ $report->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if ($report->status === 'submitted')
                                            <span class="badge bg-secondary">Enviado</span>
                                        @elseif ($report->status === 'under_review')
                                            <span class="badge bg-info text-dark">En revisión</span>
                                        @elseif ($report->status === 'resolved_valid' || $report->status === 'resolved_invalid')
                                            <span class="badge bg-success">Revisado</span>
                                        @else
                                            <span class="badge bg-light text-dark border">Cerrado</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $submittedReports->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
