@extends('layouts.app')

@section('title', 'Bandeja de Moderación y Reportes | Administración')

@section('content')
<section class="admin-page py-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <p class="text-uppercase text-muted fw-bold small mb-0">Moderación y Disciplina</p>
                <h1 class="h3 mb-0">Bandeja de Reportes</h1>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.reports.appeals') }}" class="btn btn-outline-warning position-relative">
                    <i class="bi bi-arrow-return-left"></i> Apelaciones
                    @if ($pendingAppealsCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ $pendingAppealsCount }}
                        </span>
                    @endif
                </a>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.reports.index') }}">
                    <div class="col-12 col-md-4">
                        <label for="status" class="form-label small fw-bold text-muted">Filtrar por estado:</label>
                        <select class="form-select form-select-sm" name="status" id="status">
                            <option value="">Todos los estados</option>
                            <option value="submitted" @selected(request('status') === 'submitted')>Enviados (Nuevos)</option>
                            <option value="under_review" @selected(request('status') === 'under_review')>En revisión</option>
                            <option value="resolved_valid" @selected(request('status') === 'resolved_valid')>Válidos (Sancionados)</option>
                            <option value="resolved_invalid" @selected(request('status') === 'resolved_invalid')>Inválidos (Sin sanción)</option>
                            <option value="closed" @selected(request('status') === 'closed')>Cerrados</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="category" class="form-label small fw-bold text-muted">Filtrar por categoría:</label>
                        <select class="form-select form-select-sm" name="category" id="category">
                            <option value="">Todas las categorías</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->value }}" @selected(request('category') === $cat->value)>{{ $cat->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-funnel"></i> Filtrar</button>
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Folio</th>
                                <th>Denunciante</th>
                                <th>Denunciado</th>
                                <th>Motivo / Categoría</th>
                                <th>Severidad</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reports as $report)
                                <tr>
                                    <td><code>#REP-{{ $report->id }}</code></td>
                                    <td>
                                        <div class="fw-bold">{{ $report->reporter?->name ?? 'Usuario' }}</div>
                                        <small class="text-muted">{{ $report->reporter?->email }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-danger">{{ $report->reported?->name ?? 'Usuario' }}</div>
                                        <small class="text-muted">{{ $report->reported?->role->value }} · ID: {{ $report->reported_id }}</small>
                                    </td>
                                    <td>
                                        <span class="d-inline-block text-truncate" style="max-width: 200px;">
                                            {{ $report->category->label() }}
                                        </span>
                                        @if ($report->job_request_id)
                                            <span class="badge bg-light text-dark border d-block mt-1">Chamba #{{ $report->job_request_id }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($report->severity_reported->value === 'critical')
                                            <span class="badge bg-danger">Crítica</span>
                                        @elseif ($report->severity_reported->value === 'high')
                                            <span class="badge bg-warning text-dark">Alta</span>
                                        @elseif ($report->severity_reported->value === 'medium')
                                            <span class="badge bg-info text-dark">Media</span>
                                        @else
                                            <span class="badge bg-secondary">Baja</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($report->status === 'submitted')
                                            <span class="badge bg-primary">Nuevo</span>
                                        @elseif ($report->status === 'under_review')
                                            <span class="badge bg-info text-dark">En revisión</span>
                                        @elseif ($report->status === 'resolved_valid')
                                            <span class="badge bg-danger">Válido</span>
                                        @elseif ($report->status === 'resolved_invalid')
                                            <span class="badge bg-success">Inválido</span>
                                        @else
                                            <span class="badge bg-secondary">Cerrado</span>
                                        @endif
                                    </td>
                                    <td><small>{{ $report->created_at->format('d/m/Y H:i') }}</small></td>
                                    <td>
                                        <a href="{{ route('admin.reports.show', $report) }}" class="btn btn-outline-primary btn-sm">
                                            Revisar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        No hay reportes que coincidan con los filtros seleccionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($reports->hasPages())
                    <div class="p-3">
                        {{ $reports->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
