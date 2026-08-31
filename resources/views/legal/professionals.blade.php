@extends('layouts.public')

@section('title', 'Términos y Guía para Profesionales | Chambapp')
@section('meta_description', 'Normas, obligaciones, comisiones y directrices para profesionales en Chambapp.')

@section('content')
    <section class="legal-page py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-10 col-xl-9">
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb small">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Para Profesionales</li>
                        </ol>
                    </nav>

                    <div class="legal-header mb-4">
                        <p class="eyebrow text-primary fw-bold mb-1">Comunidad y Estándares Profesionales</p>
                        <h1 class="page-title display-6 fw-bold text-navy">Términos y Guía para Profesionales</h1>
                        <p class="text-muted small mb-0"><strong>Última actualización:</strong> 31 de agosto de 2026 &middot; <strong>Versión:</strong> 2.0-MX</p>
                    </div>

                    <div class="ui-card p-4 p-md-5 shadow-sm rounded-4 bg-white border">
                        <div class="legal-content">
                            <h2 class="h5 fw-bold text-navy mt-2 mb-3">1. Naturaleza de la Relación</h2>
                            <p>
                                Los Profesionales registrados en Chambapp operan como <strong>prestadores de servicios independientes</strong>. El registro en la Plataforma no crea una relación laboral, subordinación, sociedad, franquicia ni agencia entre el Profesional y Chambapp. El Profesional determina de forma autónoma sus horarios, herramientas de trabajo y métodos de ejecución de los servicios ofrecidos.
                            </p>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">2. Requisitos y Habilitación de Perfil</h2>
                            <p>Para recibir contrataciones y pagos en la Plataforma, el Profesional debe:</p>
                            <ul>
                                <li>Completar su perfil con información real, experiencia y fotografía profesional.</li>
                                <li>Vincular su cuenta autorizada de <strong>Mercado Pago</strong> para la recepción de dispersiones directas.</li>
                                <li>Publicar servicios claros con descripciones detalladas y precios base transparentes.</li>
                                <li>Cumplir con los procesos de verificación de identidad dispuestos por Chambapp cuando sean solicitados.</li>
                            </ul>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">3. Comisiones y Liquidación de Pagos</h2>
                            <p>
                                Por el uso de la Plataforma, infraestructura tecnológica y procesamiento de clientes, Chambapp aplica una <strong>comisión de intermediación del 15%</strong> sobre el precio base de cada Chamba completada. Los fondos se dispersan a la cuenta de Mercado Pago vinculada una vez que el Cliente confirma la finalización mediante el código de seguridad.
                            </p>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">4. Obligaciones y Buenas Prácticas</h2>
                            <ul>
                                <li><strong>Puntualidad:</strong> Respetar las fechas y horarios acordados con el Cliente en contrataciones directas y programadas.</li>
                                <li><strong>Actualización de Estado:</strong> Usar la aplicación para actualizar su progreso operativo (<em>"Voy en camino"</em>, <em>"Llegué"</em>, <em>"Iniciar trabajo"</em> y <em>"Marcar como terminado"</em>).</li>
                                <li><strong>Calidad y Respeto:</strong> Brindar un trato respetuoso, profesional y seguro en el domicilio o ubicación del Cliente.</li>
                                <li><strong>Validación Final:</strong> Solicitar el Código de Confirmación de 6 dígitos al Cliente únicamente cuando las labores hayan concluido satisfactoriamente.</li>
                            </ul>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">5. Conductas Prohibidas</h2>
                            <p>Son causales de suspensión o expulsión inmediata:</p>
                            <ul>
                                <li>Intentar desviar clientes fuera de la Plataforma o exigir pagos en efectivo no autorizados para eludir las comisiones.</li>
                                <li>Cobros indebidos no pactados en la orden de trabajo.</li>
                                <li>Incumplimiento deliberado, abandono de trabajos o trato discriminatorio.</li>
                                <li>Uso indebido de datos de contacto de clientes para fines ajenos a la prestación del servicio.</li>
                            </ul>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">6. Sistema Disciplinario, Moderación y Apelaciones</h2>
                            <p>
                                Chambapp cuenta con un sistema de moderación humana. Ante reportes de clientes o incumplimientos, el equipo administrativo podrá emitir advertencias formales, aplicar suspensiones temporales o dar de baja definitiva el perfil. El Profesional tiene el derecho de apelar cualquier sanción aportando pruebas a través del canal de soporte oficial.
                            </p>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">7. Contacto para Profesionales</h2>
                            <p class="mb-0">
                                Para dudas sobre pagos, comisiones, verificación o soporte operativo, escríbenos a <a href="mailto:profesionales@chambapp.com.mx">profesionales@chambapp.com.mx</a> o visita nuestra sección de <a href="{{ route('legal.contact') }}">Contacto y Soporte</a>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
