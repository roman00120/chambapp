# Smoke test de Chambapp

Ejecutar después de cada despliegue, primero en Sandbox cuando involucre pagos.

- [ ] `GET /health` responde 200 con `status=ok`.
- [ ] Home, búsqueda, categorías y un servicio público cargan sin error.
- [ ] Registro muestra CSRF, conserva errores y no permite rol admin.
- [ ] Login correcto, incorrecto y cuenta bloqueada se comportan correctamente.
- [ ] Cliente ve solo sus trabajos y pagos; profesional solo sus servicios, solicitudes y ganancias.
- [ ] Cliente puede solicitar; profesional puede cotizar; cliente puede aceptar y ver el desglose 15% / profesional.
- [ ] Checkout crea la preferencia desde backend; el frontend no define monto ni comisión.
- [ ] Webhook firmado actualiza el pago; repetirlo no duplica efectos.
- [ ] Antes de pago no aparecen teléfono, email, WhatsApp ni dirección exacta; después solo los participantes del trabajo pagado los reciben.
- [ ] Profesional inicia y termina solo desde `paid`; cliente confirma y puede reseñar un trabajo completado.
- [ ] Cliente no puede abrir `/admin`; otro usuario recibe 403 en recursos ajenos.
- [ ] 404, 403 y 500 muestran UX segura sin stack trace.
- [ ] En móvil no aparece chat pre-pago; navegación y formularios no desbordan a 320 px.
- [ ] Manifest e iconos cargan; el service worker registra solo en HTTPS; offline muestra la página indicada.
- [ ] No se cachean dashboards, pagos, ganancias, trabajos, admin ni webhooks como fuente de verdad.
