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
