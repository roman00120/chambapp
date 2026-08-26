@extends('layouts.app')

@section('title', 'Reportar usuario o problema | Chambapp')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h1 class="h4 mb-0 text-danger"><i class="bi bi-flag-fill"></i> Reportar a un usuario</h1>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                        <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                        <div>
                            <strong>Revisión humana y justa:</strong> Un reporte no genera una sanción automática. Nuestro equipo de moderación revisará los hechos y la evidencia antes de tomar cualquier decisión.
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('reports.store') }}" enctype="multipart/form-data">
                        @csrf

                        @if ($reportedUser)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Usuario a reportar:</label>
                                <div class="p-3 bg-light rounded d-flex align-items-center">
                                    <i class="bi bi-person-circle fs-3 text-muted me-3"></i>
                                    <div>
                                        <div class="fw-bold">{{ $reportedUser->name }}</div>
                                        <small class="text-muted">{{ $reportedUser->role->value === 'professional' ? 'Profesional' : 'Cliente' }}</small>
                                    </div>
                                </div>
                                <input type="hidden" name="reported_id" value="{{ $reportedUser->id }}">
                            </div>
                        @else
                            <div class="mb-3">
                                <label for="reported_id" class="form-label fw-bold">ID o Referencia del usuario a reportar:</label>
                                <input type="number" name="reported_id" id="reported_id" class="form-control" value="{{ old('reported_id') }}" required placeholder="Ingresa el ID del usuario">
                            </div>
                        @endif

                        @if ($jobRequest)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Trabajo relacionado:</label>
                                <div class="p-2 bg-light rounded small">
                                    <strong>{{ $jobRequest->title }}</strong> (Folio #{{ $jobRequest->id }})
                                </div>
                                <input type="hidden" name="job_request_id" value="{{ $jobRequest->id }}">
                            </div>
                        @elseif (request('job_id'))
                            <input type="hidden" name="job_request_id" value="{{ request('job_id') }}">
                        @endif

                        <div class="mb-3">
                            <label for="category" class="form-label fw-bold">Motivo principal del reporte: <span class="text-danger">*</span></label>
                            <select name="category" id="category" class="form-select" required>
                                <option value="">-- Selecciona un motivo --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->value }}" {{ old('category') === $cat->value ? 'selected' : '' }}>
                                        {{ $cat->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold">¿Qué ocurrió? Describe los detalles: <span class="text-danger">*</span></label>
                            <textarea name="description" id="description" class="form-control" rows="5" minlength="10" maxlength="3000" required placeholder="Explica de forma clara lo sucedido, fechas, acuerdos incumplidos o hechos relevantes...">{{ old('description') }}</textarea>
                            <div class="form-text">Mínimo 10 caracteres. Sé lo más específico y objetivo posible.</div>
                        </div>

                        <div class="mb-4">
                            <label for="evidence" class="form-label fw-bold">Evidencia opcional (Capturas, fotos o documentos):</label>
                            <input type="file" name="evidence[]" id="evidence" class="form-control" multiple accept="image/png,image/jpeg,image/webp,application/pdf">
                            <div class="form-text">Formatos permitidos: JPG, PNG, WEBP, PDF. Máximo 5 archivos, hasta 10MB por archivo. Los archivos se almacenan de forma estrictamente privada y segura.</div>
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" name="confirm_truthfulness" id="confirm_truthfulness" class="form-check-input" value="1" {{ old('confirm_truthfulness') ? 'checked' : '' }} required>
                            <label class="form-check-label small" for="confirm_truthfulness">
                                Confirmo que la información y evidencias proporcionadas son verdaderas según mi conocimiento. Entiendo que los reportes falsos o maliciosos constituyen una violación a los términos de la plataforma.
                            </label>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ url()->previous() ?: route('account.security') }}" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-danger px-4">
                                <i class="bi bi-send-fill"></i> Enviar reporte a moderación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
