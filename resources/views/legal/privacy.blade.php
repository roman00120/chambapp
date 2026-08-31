@extends('layouts.public')

@section('title', 'Aviso de Privacidad | Chambapp')
@section('meta_description', 'Aviso de Privacidad Integral de Chambapp conforme a la legislación mexicana.')

@section('content')
    <section class="legal-page py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-10 col-xl-9">
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb small">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Aviso de Privacidad</li>
                        </ol>
                    </nav>

                    <div class="legal-header mb-4">
                        <p class="eyebrow text-primary fw-bold mb-1">Protección de Datos Personales</p>
                        <h1 class="page-title display-6 fw-bold text-navy">Aviso de Privacidad Integral</h1>
                        <p class="text-muted small mb-0"><strong>Última actualización:</strong> 31 de agosto de 2026 &middot; <strong>Vigencia:</strong> Conforme a la LFPDPPP</p>
                    </div>

                    <div class="ui-card p-4 p-md-5 shadow-sm rounded-4 bg-white border">
                        <div class="legal-content">
                            <h2 class="h5 fw-bold text-navy mt-2 mb-3">1. Identidad y Domicilio del Responsable</h2>
                            <p>
                                <strong>Chambapp</strong>, con portal web en <a href="https://chambapp.com.mx">chambapp.com.mx</a> y correo de atención en <a href="mailto:privacidad@chambapp.com.mx">privacidad@chambapp.com.mx</a>, en estricto cumplimiento con la <em>Ley Federal de Protección de Datos Personales en Posesión de los Particulares (LFPDPPP)</em> y su Reglamento, es el responsable del tratamiento legítimo, controlado e informado de sus datos personales.
                            </p>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">2. Datos Personales Recopilados</h2>
                            <p>Para la operación de la Plataforma, Chambapp recopila las siguientes categorías de datos personales:</p>
                            <ul>
                                <li><strong>Datos de Identificación y Contacto:</strong> Nombre completo, correo electrónico, número telefónico y fotografía de perfil voluntaria.</li>
                                <li><strong>Datos de Ubicación:</strong> Dirección física de la prestación del servicio, código postal, ciudad, estado y coordenadas geográficas aproximadas obtenidas mediante GPS (previo consentimiento del usuario) exclusivamente para la cotización y enlace de servicios.</li>
                                <li><strong>Datos de Profesionales:</strong> Descripción de habilidades, catálogo de servicios, rango de cobertura y precios ofertados.</li>
                                <li><strong>Datos Transaccionales:</strong> Historial de contrataciones, fecha de solicitudes, estados de pago y referencias externas de cobro.</li>
                            </ul>
                            <div class="alert alert-light border small text-muted mb-3">
                                <strong>Seguridad Financiera:</strong> Chambapp <u>NO</u> recopila ni almacena números completos de tarjetas de crédito/débito ni códigos de seguridad (CVV). El procesamiento financiero es administrado íntegramente por <strong>Mercado Pago</strong> bajo estrictos estándares de seguridad y certificación PCI-DSS.
                            </div>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">3. Finalidades del Tratamiento</h2>
                            <p><strong>Finalidades Primarias (necesarias para el servicio):</strong></p>
                            <ul>
                                <li>Creación y administración de cuentas de usuario.</li>
                                <li>Intermediación y facilitación de la contratación directa entre Clientes y Profesionales.</li>
                                <li>Envío de notificaciones transaccionales operativas (confirmación de pago, llegada del profesional, terminación de trabajos).</li>
                                <li>Gestión de la custodia y liberación de fondos a través de pasarelas de pago autorizadas.</li>
                                <li>Atención de controversias, aclaraciones y soporte a usuarios.</li>
                            </ul>
                            <p><strong>Finalidades Secundarias (no indispensables):</strong></p>
                            <ul>
                                <li>Evaluación de la calidad del servicio y encuestas de satisfacción.</li>
                                <li>Envío de boletines informativos o promociones relacionadas con la Plataforma.</li>
                            </ul>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">4. Verificación de Identidad de Profesionales (Terceros)</h2>
                            <p>
                                Para el proceso voluntario u obligatorio de verificación de identidad, Chambapp puede apoyarse en proveedores tecnológicos especializados (como Didit). La captura y validación biométrica/documental se realiza en sesiones seguras del proveedor. Chambapp conserva únicamente identificadores técnicos de estado y códigos de validación, sin almacenar imágenes crudas de documentos oficiales ni biometría.
                            </p>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">5. Transferencias de Datos Personales</h2>
                            <p>Sus datos personales solo son compartidos en los siguientes supuestos previstos por la Ley:</p>
                            <ul>
                                <li>Entre Cliente y Profesional contratados, estrictamente los datos indispensables para la coordinación y ejecución física de la Chamba (nombre de pila, teléfono de contacto y dirección del trabajo).</li>
                                <li>Con proveedores de pasarela de pago (Mercado Pago) para la liquidación y dispersión de recursos.</li>
                                <li>Autoridades competentes cuando medie mandamiento fundado y motivado conforme a la legislación mexicana.</li>
                            </ul>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">6. Ejercicio de Derechos ARCO y Revocación del Consentimiento</h2>
                            <p>
                                Usted tiene derecho a ejercer en todo momento sus derechos de <strong>Acceso, Rectificación, Cancelación y Oposición (ARCO)</strong>, así como a revocar el consentimiento otorgado para el tratamiento de sus datos.
                            </p>
                            <p>
                                Para iniciar su solicitud, envíe un correo electrónico a <a href="mailto:privacidad@chambapp.com.mx">privacidad@chambapp.com.mx</a> incluyendo:
                            </p>
                            <ol class="small text-muted">
                                <li>Nombre completo y correo electrónico registrado en su cuenta de Chambapp.</li>
                                <li>Identificación oficial que acredite la titularidad de los datos.</li>
                                <li>Descripción clara y precisa de los datos sobre los que busca ejercer su derecho ARCO.</li>
                            </ol>
                            <p class="small text-muted mb-0">
                                Las solicitudes serán atendidas y respondidas en un plazo máximo de 20 días hábiles conforme a los términos de la LFPDPPP.
                            </p>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">7. Modificaciones al Aviso de Privacidad</h2>
                            <p class="mb-0">
                                Chambapp se reserva el derecho de modificar o actualizar el presente Aviso de Privacidad en cualquier momento. Cualquier cambio relevante será publicado en esta misma sección y, cuando aplique, notificado a través de la Plataforma.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
