@extends('layouts.app')

@section('title', 'Bandeja de Apelaciones | Administración')

@section('content')
<section class="admin-page py-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="{{ route('admin.reports.index') }}" class="btn btn-link text-decoration-none p-0 mb-1">
                    <i class="bi bi-arrow-left"></i> Volver a reportes
                </a>
                <h1 class="h3 mb-0">Bandeja de Apelaciones Disciplinarias</h1>
            </div>
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

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Folio</th>
                                <th>Usuario apelante</th>
                                <th>Sanción apelada</th>
                                <th>Argumento de apelación</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($appeals as $appeal)
                                <tr>
                                    <td><code>#APL-{{ $appeal->id }}</code></td>
                                    <td>
                                        <div class="fw-bold">{{ $appeal->user?->name ?? 'Usuario' }}</div>
                                        <small class="text-muted">{{ $appeal->user?->email }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark">{{ $appeal->disciplinaryAction?->action_type?->label() }}</span>
                                        <small class="d-block text-muted mt-1">{{ $appeal->disciplinaryAction?->reason_text }}</small>
                                    </td>
                                    <td>
                                        <p class="mb-0 small text-dark" style="max-width: 300px; white-space: pre-wrap;">{{ $appeal->appeal_text }}</p>
                                    </td>
                                    <td>
                                        @if ($appeal->status->value === 'submitted')
                                            <span class="badge bg-primary">Pendiente</span>
                                        @elseif ($appeal->status->value === 'accepted')
                                            <span class="badge bg-success">Aceptada (Revocada)</span>
                                        @elseif ($appeal->status->value === 'rejected')
                                            <span class="badge bg-danger">Rechazada</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $appeal->status->label() }}</span>
                                        @endif
                                    </td>
                                    <td><small>{{ $appeal->created_at->format('d/m/Y H:i') }}</small></td>
                                    <td>
                                        @if ($appeal->status->value === 'submitted')
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#resolveAppealModal{{ $appeal->id }}">
                                                Resolver
                                            </button>

                                            <!-- Modal Resolver Apelación -->
                                            <div class="modal fade" id="resolveAppealModal{{ $appeal->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST" action="{{ route('admin.reports.appeals.resolve', $appeal) }}">
                                                            @csrf
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Resolver Apelación #{{ $appeal->id }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p class="mb-2"><strong>Usuario:</strong> {{ $appeal->user?->name }} ({{ $appeal->user?->email }})</p>
                                                                <div class="p-3 bg-light rounded mb-3 small">
                                                                    <strong>Argumento:</strong><br>
                                                                    {{ $appeal->appeal_text }}
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Decisión administrativa:</label>
                                                                    <div class="form-check mb-2">
                                                                        <input class="form-check-input" type="radio" name="accepted" id="acc_yes{{ $appeal->id }}" value="1" required>
                                                                        <label class="form-check-label text-success fw-bold" for="acc_yes{{ $appeal->id }}">
                                                                            Aceptar apelación (Revocar sanción inmediatamente)
                                                                        </label>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="radio" name="accepted" id="acc_no{{ $appeal->id }}" value="0" required>
                                                                        <label class="form-check-label text-danger fw-bold" for="acc_no{{ $appeal->id }}">
                                                                            Rechazar apelación (Mantener sanción activa)
                                                                        </label>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label for="res_notes{{ $appeal->id }}" class="form-label fw-bold">Notas de resolución:</label>
                                                                    <textarea name="resolution_notes" id="res_notes{{ $appeal->id }}" class="form-control" rows="3" placeholder="Fundamento de la decisión..."></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" class="btn btn-primary">Guardar resolución</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted small">Resuelta por {{ $appeal->reviewer?->name ?? 'Admin' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        No hay apelaciones registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($appeals->hasPages())
                    <div class="p-3">
                        {{ $appeals->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
