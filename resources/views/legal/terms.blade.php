@extends('layouts.public')

@section('title', 'Términos y Condiciones | Chambapp')
@section('meta_description', 'Términos y Condiciones de uso de la plataforma Chambapp.')

@section('content')
    <section class="legal-page py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-10 col-xl-9">
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb small">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Términos y Condiciones</li>
                        </ol>
                    </nav>

                    <div class="legal-header mb-4">
                        <p class="eyebrow text-primary fw-bold mb-1">Marco Legal y Regulatorio</p>
                        <h1 class="page-title display-6 fw-bold text-navy">Términos y Condiciones de Uso</h1>
                        <p class="text-muted small mb-0"><strong>Última actualización:</strong> 31 de agosto de 2026 &middot; <strong>Versión:</strong> 2.0-MX</p>
                    </div>

                    <div class="alert alert-light border shadow-sm p-3 mb-4 rounded-3">
                        <div class="d-flex align-items-center gap-2 mb-2 text-primary fw-bold">
                            <i class="bi bi-shield-check fs-5"></i>
                            <span>Resumen de Operación de la Plataforma</span>
                        </div>
                        <p class="small text-muted mb-0">
                            Chambapp es una plataforma tecnológica de intermediación (marketplace) que permite a usuarios clientes conectar y contratar directamente con prestadores de servicios independientes ("Profesionales"). Chambapp no es una empresa de servicios generales, no presta directamente los trabajos contratados ni mantiene relación laboral de subordinación con los Profesionales independientes.
                        </p>
                    </div>

                    <div class="ui-card p-4 p-md-5 shadow-sm rounded-4 bg-white border">
                        <div class="legal-content">
                            <h2 class="h5 fw-bold text-navy mt-2 mb-3">1. Identificación y Alcance de la Plataforma</h2>
                            <p>
                                Los presentes Términos y Condiciones ("Términos") rigen el acceso y uso del sitio web <a href="https://chambapp.com.mx">chambapp.com.mx</a>, así como de las aplicaciones móviles y servicios relacionados (colectivamente, "Chambapp" o la "Plataforma"). Al crear una cuenta, acceder o utilizar la Plataforma, usted reconoce que ha leído, entendido y aceptado quedar legalmente vinculado por estos Términos.
                            </p>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">2. Definiciones</h2>
                            <ul>
                                <li><strong>Plataforma:</strong> El software, sitio web, aplicaciones móviles y servicios digitales provistos bajo la marca Chambapp.</li>
                                <li><strong>Cliente:</strong> Cualquier persona física o moral debidamente registrada que busca o contrata servicios a través de Chambapp.</li>
                                <li><strong>Profesional:</strong> Prestador independiente de servicios debidamente registrado que publica su oferta y presta servicios directos a Clientes.</li>
                                <li><strong>Servicio:</strong> La actividad, catálogo de labores o trabajo profesional ofertado por un Profesional en la Plataforma.</li>
                                <li><strong>Chamba (o Trabajo):</strong> La solicitud, contratación específica y orden de trabajo formalizada entre un Cliente y un Profesional.</li>
                                <li><strong>Contratación:</strong> El acuerdo directo pactado entre Cliente y Profesional para la prestación de un Servicio determinado a un precio acordado.</li>
                            </ul>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">3. Registro y Cuentas de Usuario</h2>
                            <p>
                                Para utilizar las funciones transaccionales de Chambapp, es indispensable registrarse proporcionando información verídica, exacta y actualizada (nombre completo, correo electrónico y número de teléfono). Cada usuario es responsable de mantener la confidencialidad de sus credenciales y de todas las actividades efectuadas desde su cuenta. Queda estrictamente prohibida la cesión, venta o transferencia de cuentas a terceros.
                            </p>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">4. Requisitos para Clientes</h2>
                            <p>
                                Los Clientes deben ser mayores de 18 años con capacidad legal para contratar. El Cliente se compromete a proporcionar descripciones claras de sus requerimientos, asegurar condiciones seguras para la prestación física del servicio en su domicilio o ubicación acordada, y liquidar los montos correspondientes conforme a las tarifas acordadas.
                            </p>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">5. Requisitos para Profesionales y Verificación</h2>
                            <p>
                                Los Profesionales que ofrecen servicios operan como contratistas independientes. Para habilitar la recepción de contrataciones y cobros, los Profesionales deben completar su perfil profesional, vincular su cuenta autorizada de Mercado Pago y, cuando resulte aplicable, superar los procesos de verificación de identidad dispuestos por la Plataforma.
                            </p>
                            <p class="small text-muted">
                                <em>Nota:</em> La insignia de verificación de identidad acredita la coincidencia técnica de documentos oficiales mediante proveedores especializados, pero no constituye un aval, garantía comercial ni certificación técnica de calidad sobre los resultados de las labores desempeñadas.
                            </p>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">6. Modalidades de Contratación (Marketplace Directo)</h2>
                            <p>
                                Chambapp opera bajo un modelo de <strong>Marketplace Directo</strong> donde el Cliente siempre tiene la libertad de seleccionar y contratar al Profesional de su elección:
                            </p>
                            <ul>
                                <li><strong>Contratación desde Catálogo:</strong> El Cliente explora la tienda del Profesional, revisa el precio base publicado y formaliza la contratación directa.</li>
                                <li><strong>Chamba Ahora:</strong> El Cliente selecciona una categoría de atención inmediata, visualiza a los Profesionales disponibles y elige directamente a su prestador.</li>
                                <li><strong>Chamba Programada:</strong> El Cliente define la fecha, turno y necesidad específica, seleccionando de la lista de Profesionales compatibles.</li>
                            </ul>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">7. Precios, Comisiones y Procesamiento de Pagos</h2>
                            <p>
                                Todos los precios en la Plataforma se expresan en Pesos Mexicanos (MXN). Las transacciones se procesan de forma segura a través de <strong>Mercado Pago</strong>.
                            </p>
                            <ul>
                                <li><strong>Tarifa del Cliente:</strong> Al contratar, el Cliente visualiza el precio base del servicio más la tarifa de servicio de plataforma aplicable (15%), conformando el total a pagar.</li>
                                <li><strong>Comisión del Profesional:</strong> Del precio base acordado, la Plataforma retiene la comisión por intermediación y gestión tecnológica acordada (15%).</li>
                                <li><strong>Custodia y Liberación de Fondos:</strong> Una vez aprobado el pago por Mercado Pago, el dinero permanece en custodia de la pasarela y no se libera al saldo disponible del Profesional hasta que el Cliente confirme la finalización exitosa del trabajo mediante el código de seguridad o se resuelva favorablemente una disputa.</li>
                            </ul>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">8. Código de Finalización de Trabajo</h2>
                            <p>
                                Al concluir las labores físicas acordadas, el Profesional marcará el trabajo como terminado desde su interfaz. El sistema generará un <strong>Código de Confirmación de 6 dígitos</strong> con vigencia de 24 horas remitido al Cliente. La entrega y registro de este código en la Plataforma constituye la aceptación formal del trabajo y detona la liberación del pago correspondiente.
                            </p>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">9. Cancelaciones, Disputas y Reembolsos</h2>
                            <p>
                                Las cancelaciones y reembolsos se rigen por la <a href="{{ route('legal.cancellations') }}">Política de Cancelaciones y Reembolsos</a>. Tanto el Cliente como el Profesional pueden cancelar una solicitud antes de que el Profesional inicie el trabajo. En caso de desacuerdo sobre la calidad o cumplimiento del servicio, cualquiera de las partes puede abrir una disputa formal antes de confirmar la finalización.
                            </p>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">10. Conductas Prohibidas y Fraude</h2>
                            <p>Queda estrictamente prohibido en la Plataforma:</p>
                            <ul>
                                <li>Pactar pagos por fuera de la Plataforma con el fin de evadir las tarifas de servicio y protecciones transaccionales.</li>
                                <li>Suplantar la identidad de terceros o utilizar documentación apócrifa.</li>
                                <li>Publicar contenido ofensivo, fraudulento, difamatorio o que vulnere derechos de propiedad intelectual.</li>
                                <li>Acosar, amenazar, discriminar o incurrir en conductas violentas hacia cualquier miembro de la comunidad.</li>
                            </ul>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">11. Sistema Disciplinario y Moderación</h2>
                            <p>
                                Chambapp implementa un sistema disciplinario con revisión humana obligatoria. Ante reportes fundados o infracciones a estos Términos, la administración podrá emitir advertencias formales, suspensiones temporales o bloqueos definitivos de cuenta. En casos graves de fraude o riesgo a la integridad física de los usuarios, la suspensión podrá ser inmediata. Todo usuario sancionado conserva el derecho a presentar una apelación debidamente fundamentada para su reevaluación.
                            </p>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">12. Limitación de Responsabilidad</h2>
                            <p>
                                En la máxima medida permitida por la legislación mexicana aplicable, Chambapp no asume responsabilidad por daños indirectos, incidentales, perjuicios, pérdidas de oportunidad o daños materiales resultantes de la ejecución física de los servicios contratados entre usuarios. La responsabilidad de la Plataforma frente a cualquier usuario se limitará estrictamente al monto total de las tarifas de intermediación efectivamente percibidas por Chambapp en la transacción específica que originó la reclamación.
                            </p>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">13. Legislación Aplicable y Jurisdicción</h2>
                            <p>
                                Los presentes Términos se rigen e interpretan de conformidad con las leyes federales de los Estados Unidos Mexicanos y la Ley Federal de Protección al Consumidor. Para cualquier controversia derivada del uso de la Plataforma que no pueda resolverse de mutuo acuerdo, las partes se someten a la jurisdicción de los tribunales competentes en la ciudad de Guadalajara, Jalisco, renunciando expresamente a cualquier otro fuero que pudiera corresponderles por razón de sus domicilios presentes o futuros.
                            </p>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">14. Contacto y Soporte Legal</h2>
                            <p class="mb-0">
                                Para dudas, aclaraciones o requerimientos legales respecto a estos Términos, puede contactarnos a través de nuestra página de <a href="{{ route('legal.contact') }}">Contacto y Soporte</a> o directamente al correo <a href="mailto:soporte@chambapp.com.mx">soporte@chambapp.com.mx</a>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
