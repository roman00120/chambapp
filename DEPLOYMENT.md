# Despliegue de Chambapp en Hostinger Business

Este procedimiento corresponde al MVP Laravel 13 / PHP 8.3. Es un despliegue de aplicación web responsive + PWA, no una app nativa.

## Dominio, SSL y document root

1. Configura el dominio y activa el SSL de Hostinger.
2. Usa HTTPS antes de habilitar login, OAuth o webhooks.
3. Si el plan lo permite, apunta el document root a `public/`; el resto del proyecto debe permanecer fuera del directorio público.
4. Si no puedes cambiarlo, usa la estrategia Laravel/Apache documentada por Hostinger para exponer únicamente `public/index.php`. No copies `.env`, `app/`, `config/` ni `storage/` al document root.

## Código y dependencias

```bash
git clone <repositorio> app
cd app
composer install --no-dev --optimize-autoloader
```

Compila los assets antes de subirlos desde una máquina con Node:

```bash
npm ci
npm run build
```

Sube `public/build` junto con el código. Node no debe ejecutarse permanentemente en Hostinger.

## Variables `.env`

Copia `.env.example` a `.env` en el root privado y reemplaza los valores:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio-real.example
APP_KEY=<generada-en-el-servidor>
APP_TIMEZONE=America/Mexico_City
DB_CONNECTION=mysql
DB_HOST=<host-mysql>
DB_PORT=3306
DB_DATABASE=<base>
DB_USERNAME=<usuario>
DB_PASSWORD=<secreto>
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
FILESYSTEM_DISK=public
MAIL_MAILER=smtp
MAIL_HOST=<smtp>
MAIL_PORT=587
MAIL_USERNAME=<usuario>
MAIL_PASSWORD=<secreto>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=<correo-real>
MERCADOPAGO_CLIENT_ID=<credencial-del-entorno-correcto>
MERCADOPAGO_CLIENT_SECRET=<secreto-del-entorno-correcto>
MERCADOPAGO_WEBHOOK_SECRET=<secreto-del-webhook>
CHAMBAPP_PLATFORM_FEE_PERCENT=15
```

Genera `APP_KEY` una sola vez con `php artisan key:generate --force`. No reutilices la clave local ni subas `.env` al repositorio.

## Base de datos y storage

1. Crea una base MySQL vacía en Hostinger.
2. Haz un respaldo si la base ya tiene datos.
3. Ejecuta `php artisan migrate --force`.
4. No ejecutes seeders demo ni `migrate:fresh` en producción.
5. Ejecuta `php artisan storage:link` si el enlace no existe.
6. Concede escritura únicamente a `storage/` y `bootstrap/cache`; no uses `777` como configuración permanente.

## Cachés de Laravel

Después de validar `.env`:

```bash
php artisan optimize
php artisan view:cache
```

Para limpiar sin borrar datos: `php artisan optimize:clear`.

## Mercado Pago

Configura credenciales separadas para Sandbox y producción. La URL OAuth debe coincidir exactamente con:

`https://tu-dominio-real.example/profesional/pagos/configuracion/oauth/callback`

Configura el Webhook firmado en:

`https://tu-dominio-real.example/webhooks/mercadopago`

Prueba primero con las cuentas de prueba permitidas por Mercado Pago. Los callbacks solo muestran UX: la confirmación real llega por Webhook y consulta la API. No actives cobros reales con credenciales falsas o sin validar la firma.

## Administrador inicial

No conserves `admin@chambapp.test` ni contraseñas demo. Crea o promueve el administrador desde SSH o un procedimiento SQL controlado, usando contraseña única. Nunca guardes esa contraseña en logs.

## Pruebas posteriores

Ejecuta [SMOKE_TEST.md](SMOKE_TEST.md), confirma `/health`, revisa `storage/logs/laravel-*.log`, prueba email de reset/verificación y valida OAuth/Webhook en Sandbox antes de abrir tráfico.

## Respaldos y recuperación

Activa los backups de Hostinger y conserva una copia de la base MySQL y de `storage/app/public`. Antes de una migración importante: backup, ventana de mantenimiento (`php artisan down`), migración, cachés y smoke test. Para recuperar: restaura DB y uploads, coloca `.env`, ejecuta `php artisan optimize` y verifica `/health`.
## Validación de Fase 13

Prueba sobre HTTPS y móvil: crear una chamba inmediata, negar geolocalización y completar dirección manual, recibir una invitación, aceptar/cotizar, aprobar pago y recorrer en-camino/llegada/inicio. Confirma que el polling no devuelve dirección, teléfono ni coordenadas y que una ubicación con más de 30 minutos no recibe invitaciones. Variables opcionales: `CHAMBAPP_IMMEDIATE_TIMEOUT=5`, `CHAMBAPP_INVITATION_TIMEOUT=3` y `CHAMBAPP_LOCATION_FRESHNESS=30`.
