@extends('layouts.public')

@section('title', 'Contacto y Soporte | Chambapp')
@section('meta_description', 'Comunícate con el equipo de soporte y atención a usuarios de Chambapp.')

@section('content')
    <section class="legal-page py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-10 col-xl-9">
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb small">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Contacto y Soporte</li>
                        </ol>
                    </nav>

                    <div class="legal-header mb-4">
                        <p class="eyebrow text-primary fw-bold mb-1">Atención a la Comunidad</p>
                        <h1 class="page-title display-6 fw-bold text-navy">Contacto y Soporte Chambapp</h1>
                        <p class="text-muted small mb-0">Estamos disponibles para asistirte en tus contrataciones, dudas legales y soporte técnico.</p>
                    </div>

                    <div class="row g-4 my-2">
                        <div class="col-12 col-md-6">
                            <div class="ui-card p-4 h-100 bg-white border shadow-sm rounded-4">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="p-3 bg-primary-subtle text-primary rounded-3 fs-4">
                                        <i class="bi bi-headset"></i>
                                    </div>
                                    <div>
                                        <h2 class="h6 fw-bold text-navy mb-0">Soporte a Clientes</h2>
                                        <small class="text-muted">Dudas de contratación y pagos</small>
                                    </div>
                                </div>
                                <p class="small text-muted mb-3">
                                    Si necesitas ayuda con un servicio, seguimiento de Chamba o dudas sobre un cobro de Mercado Pago.
                                </p>
                                <a class="ui-button ui-button--outline w-100 text-center" href="mailto:soporte@chambapp.com.mx">
                                    <i class="bi bi-envelope me-1"></i> soporte@chambapp.com.mx
                                </a>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="ui-card p-4 h-100 bg-white border shadow-sm rounded-4">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="p-3 bg-success-subtle text-success rounded-3 fs-4">
                                        <i class="bi bi-person-badge"></i>
                                    </div>
                                    <div>
                                        <h2 class="h6 fw-bold text-navy mb-0">Atención a Profesionales</h2>
                                        <small class="text-muted">Perfil, verificación y comisiones</small>
                                    </div>
                                </div>
                                <p class="small text-muted mb-3">
                                    Para asesoría en la vinculación de Mercado Pago, publicación de servicios o aclaración de comisiones.
                                </p>
                                <a class="ui-button ui-button--outline w-100 text-center" href="mailto:profesionales@chambapp.com.mx">
                                    <i class="bi bi-envelope me-1"></i> profesionales@chambapp.com.mx
                                </a>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="ui-card p-4 h-100 bg-white border shadow-sm rounded-4">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="p-3 bg-warning-subtle text-warning-emphasis rounded-3 fs-4">
                                        <i class="bi bi-shield-lock"></i>
                                    </div>
                                    <div>
                                        <h2 class="h6 fw-bold text-navy mb-0">Privacidad y Datos (ARCO)</h2>
                                        <small class="text-muted">Ejercicio de derechos conforme a la ley</small>
                                    </div>
                                </div>
                                <p class="small text-muted mb-3">
                                    Para solicitudes de acceso, rectificación, cancelación u oposición al tratamiento de datos personales.
                                </p>
                                <a class="ui-button ui-button--outline w-100 text-center" href="mailto:privacidad@chambapp.com.mx">
                                    <i class="bi bi-envelope me-1"></i> privacidad@chambapp.com.mx
                                </a>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="ui-card p-4 h-100 bg-white border shadow-sm rounded-4">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="p-3 bg-danger-subtle text-danger rounded-3 fs-4">
                                        <i class="bi bi-exclamation-octagon"></i>
                                    </div>
                                    <div>
                                        <h2 class="h6 fw-bold text-navy mb-0">Disputas y Reportes</h2>
                                        <small class="text-muted">Mediación y resolución de problemas</small>
                                    </div>
                                </div>
                                <p class="small text-muted mb-3">
                                    Para inconformidades con trabajos en curso o reporte de conductas que violen las normas de la comunidad.
                                </p>
                                <a class="ui-button ui-button--outline w-100 text-center" href="mailto:disputas@chambapp.com.mx">
                                    <i class="bi bi-envelope me-1"></i> disputas@chambapp.com.mx
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="ui-card p-4 mt-4 bg-white border rounded-4 shadow-sm">
                        <h2 class="h6 fw-bold text-navy mb-2"><i class="bi bi-geo-alt me-1"></i> Ubicación Operativa</h2>
                        <p class="small text-muted mb-0">
                            Chambapp opera digitalmente con sede en <strong>Guadalajara, Jalisco, México</strong>. Las solicitudes y correos son atendidos en días hábiles en un horario de 9:00 a 18:00 hrs (Tiempo del Centro de México).
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
