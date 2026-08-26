@extends('layouts.app')

@section('title', 'Detalle de Reporte #'.$report->id.' | Administración')

@section('content')
<section class="admin-page py-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="{{ route('admin.reports.index') }}" class="btn btn-link text-decoration-none p-0 mb-1">
                    <i class="bi bi-arrow-left"></i> Volver a reportes
                </a>
                <h1 class="h3 mb-0">Reporte <code>#REP-{{ $report->id }}</code></h1>
            </div>
            <div>
                @if ($report->status === 'submitted')
                    <span class="badge bg-primary fs-6">Nuevo / Pendiente</span>
                @elseif ($report->status === 'resolved_valid')
                    <span class="badge bg-danger fs-6">Resuelto: Válido (Sancionado)</span>
                @elseif ($report->status === 'resolved_invalid')
                    <span class="badge bg-success fs-6">Resuelto: Inválido (Sin sanción)</span>
                @else
                    <span class="badge bg-secondary fs-6">{{ ucfirst($report->status) }}</span>
                @endif
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

        <div class="row g-4">
            <!-- Columna Izquierda: Información de las Partes y Caso -->
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0"><i class="bi bi-chat-left-text"></i> Hechos reportados</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-sm-6">
                                <span class="text-muted small d-block">Categoría:</span>
                                <strong class="fs-6">{{ $report->category->label() }}</strong>
                            </div>
                            <div class="col-12 col-sm-6">
                                <span class="text-muted small d-block">Severidad declarada:</span>
                                <span class="badge bg-secondary">{{ $report->severity_reported->label() }}</span>
                            </div>
                            @if ($report->jobRequest)
                                <div class="col-12">
                                    <span class="text-muted small d-block">Trabajo relacionado:</span>
                                    <div class="p-2 bg-light rounded mt-1">
                                        <strong>{{ $report->jobRequest->title }}</strong> · Folio #{{ $report->jobRequest->id }} · Estado: {{ $report->jobRequest->status->value }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        <h6 class="fw-bold mb-2">Descripción del denunciante:</h6>
                        <div class="p-3 bg-light rounded mb-4" style="white-space: pre-wrap;">{{ $report->description }}</div>

                        <h6 class="fw-bold mb-2">Evidencias adjuntas ({{ $report->evidence->count() }}):</h6>
                        @if ($report->evidence->isEmpty())
                            <p class="text-muted small">No se adjuntaron archivos de evidencia.</p>
                        @else
                            <div class="list-group">
                                @foreach ($report->evidence as $item)
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="bi bi-file-earmark-check text-primary me-2"></i>
                                            <strong>{{ $item->original_name }}</strong>
                                            <small class="text-muted ms-2">({{ round($item->file_size / 1024, 1) }} KB)</small>
                                        </div>
                                        <a href="{{ route('admin.reports.evidence.download', $item) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-download"></i> Descargar de forma segura
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Formulario de Resolución Administrativa -->
                @if (in_array($report->status, ['submitted', 'under_review'], true))
                    <div class="card shadow-sm border-0 border-top border-primary border-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0 text-primary"><i class="bi bi-check2-square"></i> Decisión y Resolución Administrativa</h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST" action="{{ route('admin.reports.resolve', $report) }}">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Selecciona la decisión: <span class="text-danger">*</span></label>
                                    
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="decision" id="dec_invalid" value="invalid" required onchange="toggleFields()">
                                        <label class="form-check-label" for="dec_invalid">
                                            <strong>Reporte No Válido / Infundado:</strong> Cerrar reporte. NO emite ninguna tarjeta ni sanción disciplinaria.
                                        </label>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="decision" id="dec_yellow" value="valid_yellow_card" required onchange="toggleFields()">
                                        <label class="form-check-label" for="dec_yellow">
                                            <strong>Reporte Válido → Emitir Tarjeta Amarilla:</strong> Registra una advertencia disciplinaria activa al usuario reportado.
                                        </label>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="decision" id="dec_severe" value="valid_severe" required onchange="toggleFields()">
                                        <label class="form-check-label" for="dec_severe">
                                            <strong>Reporte Válido → Sanción Grave Directa:</strong> Para casos críticos (fraude, violencia, amenazas) aplica suspensión o bloqueo sin requerir 3 amarillas.
                                        </label>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="decision" id="dec_close" value="close_no_action" required onchange="toggleFields()">
                                        <label class="form-check-label" for="dec_close">
                                            <strong>Cerrar sin acción disciplinaria:</strong> Finalizar trámite sin sanción formal.
                                        </label>
                                    </div>
                                </div>

                                <div id="severeFields" class="d-none p-3 bg-light rounded mb-3">
                                    <div class="mb-3">
                                        <label for="action_type" class="form-label fw-bold">Tipo de sanción grave:</label>
                                        <select name="action_type" id="action_type" class="form-select">
                                            <option value="temporary_suspension">Suspensión Temporal</option>
                                            <option value="indefinite_suspension">Suspensión Indefinida</option>
                                            <option value="ban">Bloqueo Definitivo de Cuenta</option>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label for="suspension_days" class="form-label fw-bold">Días de suspensión temporal (si aplica):</label>
                                        <input type="number" name="suspension_days" id="suspension_days" class="form-control" min="1" max="365" placeholder="Ej: 15">
                                    </div>
                                </div>

                                <div id="reasonFields" class="mb-3 d-none">
                                    <label for="reason_text" class="form-label fw-bold">Motivo formal para el usuario sancionado: <span class="text-danger">*</span></label>
                                    <textarea name="reason_text" id="reason_text" class="form-control" rows="2" placeholder="Este mensaje será visible para el usuario en su aviso disciplinario."></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="admin_notes_private" class="form-label fw-bold">Notas privadas de auditoría administrativa (No visibles para usuarios):</label>
                                    <textarea name="admin_notes_private" id="admin_notes_private" class="form-control" rows="3" placeholder="Contexto de investigación, justificación de moderación, etc."></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-shield-check"></i> Aplicar y guardar resolución
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="card shadow-sm border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold mb-2">Resolución registrada:</h6>
                            <p class="mb-1"><strong>Resultado:</strong> {{ $report->resolution }}</p>
                            <p class="mb-1"><strong>Revisado por:</strong> {{ $report->reviewer?->name ?? 'Administración' }}</p>
                            <p class="mb-1"><strong>Fecha de resolución:</strong> {{ $report->reviewed_at?->format('d/m/Y H:i') }}</p>
                            @if ($report->admin_notes_private)
                                <div class="mt-2 p-2 bg-white rounded border">
                                    <small class="text-muted d-block fw-bold">Notas privadas de moderación:</small>
                                    <small>{{ $report->admin_notes_private }}</small>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Columna Derecha: Partes y Auditoría del Denunciado -->
            <div class="col-12 col-lg-4">
                <!-- Denunciante -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="card-title mb-0"><i class="bi bi-person"></i> Denunciante</h6>
                    </div>
                    <div class="card-body">
                        <div class="fw-bold">{{ $report->reporter?->name ?? 'Usuario' }}</div>
                        <small class="text-muted d-block">{{ $report->reporter?->email }}</small>
                        <div class="mt-2">
                            <span class="small text-muted d-block">Capacidades:</span>
                            <span class="badge bg-light text-dark border">
                                {{ implode(', ', array_map(fn($c) => ucfirst($c), $report->reporter?->capabilities() ?? [$report->reporter?->role?->value ?? 'usuario'])) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Denunciado & Historial Disciplinario Privado -->
                <div class="card shadow-sm border-0 border-start border-danger border-4 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="card-title mb-0 text-danger"><i class="bi bi-person-x"></i> Usuario Denunciado</h6>
                    </div>
                    <div class="card-body">
                        <div class="fw-bold fs-6">{{ $report->reported?->name ?? 'Usuario' }}</div>
                        <small class="text-muted d-block">{{ $report->reported?->email }}</small>
                        <small class="text-muted d-block">User ID: {{ $report->reported_id }}</small>
                        <div class="mt-2">
                            <span class="small text-muted d-block">Capacidades:</span>
                            <span class="badge bg-light text-danger border border-danger">
                                {{ implode(', ', array_map(fn($c) => ucfirst($c), $report->reported?->capabilities() ?? [$report->reported?->role?->value ?? 'usuario'])) }}
                            </span>
                        </div>

                        <hr>
                        <h6 class="fw-bold small text-uppercase text-muted mb-2">Historial Disciplinario de la Cuenta (User #{{ $report->reported_id }}):</h6>
                        
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                            <span class="small">Tarjetas amarillas activas:</span>
                            <span class="badge {{ $activeYellowCards > 0 ? 'bg-warning text-dark' : 'bg-success' }} fs-6">{{ $activeYellowCards }}</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded">
                            <span class="small">Reportes previos recibidos:</span>
                            <span class="badge bg-secondary fs-6">{{ $totalPreviousReports }}</span>
                        </div>

                        <div class="alert alert-warning p-2 small mb-0">
                            <i class="bi bi-info-circle"></i> <strong>Nota:</strong> Los reportes previos no confirmados no implican culpabilidad. Valida cada caso por sus propias evidencias.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function toggleFields() {
    const isYellow = document.getElementById('dec_yellow').checked;
    const isSevere = document.getElementById('dec_severe').checked;
    
    document.getElementById('reasonFields').classList.toggle('d-none', !isYellow && !isSevere);
    document.getElementById('severeFields').classList.toggle('d-none', !isSevere);
}
</script>
@endsection
