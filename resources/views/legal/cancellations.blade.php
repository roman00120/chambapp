@extends('layouts.public')

@section('title', 'Política de Cancelaciones y Reembolsos | Chambapp')
@section('meta_description', 'Conoce las reglas de cancelación, disputas y reembolsos en Chambapp.')

@section('content')
    <section class="legal-page py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-10 col-xl-9">
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb small">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Cancelaciones y Reembolsos</li>
                        </ol>
                    </nav>

                    <div class="legal-header mb-4">
                        <p class="eyebrow text-primary fw-bold mb-1">Garantía y Protección Transaccional</p>
                        <h1 class="page-title display-6 fw-bold text-navy">Política de Cancelaciones y Reembolsos</h1>
                        <p class="text-muted small mb-0"><strong>Última actualización:</strong> 31 de agosto de 2026 &middot; <strong>Versión:</strong> 2.0-MX</p>
                    </div>

                    <div class="ui-card p-4 p-md-5 shadow-sm rounded-4 bg-white border">
                        <div class="legal-content">
                            <h2 class="h5 fw-bold text-navy mt-2 mb-3">1. Principio de Custodia Segura</h2>
                            <p>
                                Para proteger a ambas partes, los fondos pagados por el Cliente quedan retenidos en custodia a través de <strong>Mercado Pago</strong> y <u>no son entregados</u> al Profesional hasta que el trabajo haya sido físicamente realizado y validado mediante el <strong>Código de Finalización de 6 dígitos</strong>.
                            </p>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">2. Cancelación Antes del Inicio del Trabajo</h2>
                            <div class="row g-3 my-2">
                                <div class="col-12 col-md-6">
                                    <div class="p-3 border rounded-3 h-100 bg-light">
                                        <h3 class="h6 fw-bold text-navy mb-2"><i class="bi bi-person me-1"></i> Cancelación por el Cliente</h3>
                                        <p class="small text-muted mb-0">
                                            El Cliente puede cancelar la solicitud sin penalización en cualquier momento antes de que el Profesional inicie el trabajo. Si el pago ya fue aprobado, se detonará el proceso de reembolso conforme a los tiempos de la pasarela.
                                        </p>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="p-3 border rounded-3 h-100 bg-light">
                                        <h3 class="h6 fw-bold text-navy mb-2"><i class="bi bi-tools me-1"></i> Cancelación por el Profesional</h3>
                                        <p class="small text-muted mb-0">
                                            Si el Profesional no puede acudir o atender la solicitud antes del inicio, podrá cancelarla justificadamente. El Cliente recibirá el reembolso íntegro de los montos retenidos. Cancelaciones recurrentes injustificadas por parte del Profesional afectarán su visibilidad y rating.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">3. Proceso y Tiempos de Reembolso</h2>
                            <p>
                                Cuando una cancelación o disputa resuelta dé lugar a un reembolso, la orden de devolución se envía de inmediato a <strong>Mercado Pago</strong>:
                            </p>
                            <ul>
                                <li><strong>Saldo en Cuenta Mercado Pago / Débito:</strong> Generalmente acreditado de manera inmediata o en un plazo de 24 a 48 horas.</li>
                                <li><strong>Tarjetas de Crédito:</strong> El plazo de acreditación en el estado de cuenta bancario dependerá de la institución emisora de su tarjeta (usualmente entre 2 y 10 días hábiles).</li>
                            </ul>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">4. Disputas y Controversias</h2>
                            <p>
                                Si el Profesional concluye la labor pero el Cliente considera que el servicio fue deficiente, incompleto o no corresponde a lo pactado, el Cliente <u>no debe entregar el Código de Finalización</u> y deberá presionar el botón <strong>"Abrir disputa / Reportar problema"</strong> desde el detalle de la Chamba.
                            </p>
                            <p>
                                El equipo de mediación de Chambapp solicitará evidencia fotográfica y antecedentes a ambas partes para resolver en un plazo no mayor a 5 días hábiles la liberación total, reembolso parcial o reembolso total de la operación.
                            </p>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">5. Trabajos Completados y Validados</h2>
                            <p class="mb-0">
                                Una vez que el Cliente entrega e ingresa el Código de Confirmación de 6 dígitos en la Plataforma, el trabajo se considera formalmente <strong>Completado y Aprobado</strong>, liberando los fondos al saldo del Profesional. Posterior a este momento, no proceden reembolsos automáticos a través de la Plataforma.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
