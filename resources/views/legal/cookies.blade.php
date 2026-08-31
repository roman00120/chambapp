@extends('layouts.public')

@section('title', 'Política de Cookies | Chambapp')
@section('meta_description', 'Conoce el uso de cookies y tecnologías de almacenamiento en Chambapp.')

@section('content')
    <section class="legal-page py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-10 col-xl-9">
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb small">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Política de Cookies</li>
                        </ol>
                    </nav>

                    <div class="legal-header mb-4">
                        <p class="eyebrow text-primary fw-bold mb-1">Transparencia y Seguridad Digital</p>
                        <h1 class="page-title display-6 fw-bold text-navy">Política de Cookies</h1>
                        <p class="text-muted small mb-0"><strong>Última actualización:</strong> 31 de agosto de 2026 &middot; <strong>Versión:</strong> 1.0-MX</p>
                    </div>

                    <div class="ui-card p-4 p-md-5 shadow-sm rounded-4 bg-white border">
                        <div class="legal-content">
                            <h2 class="h5 fw-bold text-navy mt-2 mb-3">1. ¿Qué son las Cookies?</h2>
                            <p>
                                Las cookies son pequeños archivos de texto que los sitios web almacenan en su navegador o dispositivo al visitarlos. Permiten que la Plataforma recuerde sus acciones y preferencias (como inicio de sesión, idioma y opciones de seguridad) para que no tenga que volver a introducirlas cada vez que navega por nuestras páginas.
                            </p>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">2. Cookies que Utiliza Chambapp</h2>
                            <p>Chambapp utiliza exclusivamente cookies técnicas y de sesión esenciales para el correcto funcionamiento del servicio:</p>
                            
                            <div class="table-responsive my-3">
                                <table class="table table-bordered table-sm small">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nombre de la Cookie</th>
                                            <th>Tipo / Finalidad</th>
                                            <th>Duración</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>chambapp_session</code></td>
                                            <td><strong>Esencial / Autenticación:</strong> Identifica su sesión segura de usuario y mantiene su inicio de sesión activo entre páginas.</td>
                                            <td>Sesión / 2 horas</td>
                                        </tr>
                                        <tr>
                                            <td><code>XSRF-TOKEN</code></td>
                                            <td><strong>Esencial / Seguridad:</strong> Protege sus formularios contra ataques de falsificación de peticiones en sitios cruzados (CSRF).</td>
                                            <td>Sesión</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">3. Cookies de Terceros</h2>
                            <p>
                                Al procesar transacciones de pago o autenticación mediante proveedores externos autorizados (como <strong>Mercado Pago</strong> o <strong>Google Sign-In</strong>), dichos servicios pueden establecer sus propias cookies estrictamente necesarias para procesar el pago o validar sus credenciales de acceso bajo sus respectivas políticas de privacidad.
                            </p>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">4. Cómo Gestionar o Deshabilitar las Cookies</h2>
                            <p>
                                Usted puede configurar su navegador en cualquier momento para bloquear o eliminar las cookies instaladas. Tenga en cuenta que al deshabilitar las cookies esenciales (como <code>chambapp_session</code> o <code>XSRF-TOKEN</code>), no podrá iniciar sesión ni contratar servicios dentro de Chambapp.
                            </p>
                            <ul>
                                <li><strong>Google Chrome:</strong> Configuración &rarr; Privacidad y seguridad &rarr; Cookies y otros datos de sitios.</li>
                                <li><strong>Safari (iOS / macOS):</strong> Preferencias &rarr; Privacidad &rarr; Bloquear todas las cookies.</li>
                                <li><strong>Mozilla Firefox:</strong> Opciones &rarr; Privacidad y seguridad &rarr; Cookies y datos del sitio.</li>
                                <li><strong>Microsoft Edge:</strong> Configuración &rarr; Permisos del sitio &rarr; Cookies y datos almacenados.</li>
                            </ul>

                            <h2 class="h5 fw-bold text-navy mt-4 mb-3">5. Contacto</h2>
                            <p class="mb-0">
                                Si tiene preguntas sobre el uso de cookies en Chambapp, puede contactarnos a través de <a href="{{ route('legal.contact') }}">Contacto y Soporte</a> o al correo <a href="mailto:soporte@chambapp.com.mx">soporte@chambapp.com.mx</a>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
