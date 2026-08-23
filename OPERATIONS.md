# Operación de Chambapp

## Logs y errores

Los logs diarios están en `storage/logs/laravel-YYYY-MM-DD.log`. En producción usa `LOG_LEVEL=warning` o un nivel más restrictivo y revisa errores 500, `Mercado Pago webhook` y `payment.status_changed`. No pegues tokens, contraseñas, tarjetas, payloads completos ni datos personales en logs.

## Mantenimiento

Antes de una operación relevante:

```bash
php artisan down
php artisan migrate --force
php artisan optimize
php artisan up
```

No uses `migrate:fresh`, no borres uploads y no sobrescribas `.env` durante un despliegue.

## Caché y health

Para limpiar cachés de forma segura: `php artisan optimize:clear`; después usa `php artisan optimize`.

`GET /health` responde `{ "status": "ok" }` si la aplicación y la conexión DB responden. Si devuelve 503, revisa credenciales MySQL, disponibilidad del servidor y logs; no expone secretos ni versiones internas.

## Webhooks y pagos

Un fallo de firma devuelve 401. Un proveedor no disponible devuelve 202 para permitir reintento. Busca el evento general en logs y revisa `payment_transactions` por `webhook.received`, `webhook.rejected` y `payment.status_changed`. Nunca marques un `Payment` o un trabajo como pagado manualmente desde una ruta normal.

## Backups

Conserva backups de DB y `storage/app/public`. Verifica periódicamente que una copia pueda restaurarse en un entorno separado. Antes de migraciones importantes, crea un backup y anota el momento.

## No hacer en producción

- No activar `APP_DEBUG`.
- No usar HTTP para login, OAuth o Webhook.
- No subir `.env`, dumps, logs ni credenciales.
- No ejecutar `migrate:fresh` ni seeders demo.
- No alterar importes, comisión histórica o estados financieros directamente.
- No instalar herramientas debug públicas sin una decisión de seguridad explícita.
## Operación on-demand

Las búsquedas inmediatas no requieren workers: cliente y profesional hacen polling protegido por rate limit. Si una chamba permanece en `searching`, revisa `search_expires_at`, `search_round`, `search_radius_km` e invitaciones antes de intervenir. No cambies estados manualmente ni asignes profesionales fuera de `OnDemandMatchingService`.

Una invitación válida requiere cuenta activa, servicio activo de la categoría, disponibilidad explícita, ubicación fresca y distancia dentro del radio. Diagnostica con IDs, estados, distancia y timestamps; no registres direcciones, teléfonos, coordenadas de clientes ni tokens. `on_the_way` y `arrived` son eventos explícitos, no tracking continuo.

## Diagnóstico de API móvil

`GET /api/v1/health` debe responder `{"status":"ok","api_version":"v1"}` sin autenticación.

- `401 UNAUTHENTICATED`: falta el encabezado Bearer, el token es inválido o fue revocado. Inicia sesión de nuevo; nunca copies el token completo a logs.
- `403 FORBIDDEN`: el rol no corresponde, el recurso pertenece a otro usuario o la cuenta está suspendida/bloqueada. Las cuentas no activas revocan tokens.
- `409`: conflicto de dominio. Revisa `code`, por ejemplo `JOB_ALREADY_TAKEN`, `PROFESSIONAL_BUSY`, `LOCATION_STALE`, `PAYMENT_REQUIRED` o `QUOTE_EXPIRED`.
- `422`: body o coordenadas inválidas, contacto prohibido antes del pago o archivo fuera de límites. Revisa el objeto `errors`.
- `429`: límite temporal excedido. Respeta `Retry-After`; el polling legítimo está diseñado para intervalos de aproximadamente cuatro segundos.

Para CORS, confirma que el origen esté en `CHAMBAPP_CORS_ALLOWED_ORIGINS`, limpia configuración con `php artisan optimize:clear` y comprueba la petición `OPTIONS`. No habilites comodines indiscriminados. Si la autenticación falla sólo detrás de un proxy, confirma que éste reenvíe `Authorization`.

La revocación por dispositivo usa `POST /api/v1/auth/logout`; la revocación total usa `POST /api/v1/auth/logout-all`. Un restablecimiento de contraseña o bloqueo administrativo elimina todos los tokens. La expiración automática no está habilitada inicialmente; la revocación explícita y el estado activo del usuario se validan en cada petición.

El checkout nunca acepta `amount` como fuente de verdad: usa `job_requests.agreed_price` y calcula 15% en servidor. Ante diferencias, inspecciona `payments` y `payment_transactions` sin divulgar `checkout_url`, tokens del vendedor, payloads completos ni datos privados.
