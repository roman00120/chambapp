# Chambapp

Chambapp es el marketplace que estoy construyendo para conectar clientes con profesionales verificados que pueden resolver trabajos del hogar, mantenimiento y servicios locales.

La idea es sencilla: una persona publica lo que necesita, encuentra ayuda disponible cerca, recibe una cotización clara y contrata dentro de la plataforma. El contacto y la dirección exacta se mantienen protegidos hasta que el pago queda aprobado.

## Qué incluye

- Registro, inicio de sesión, verificación de correo y recuperación de contraseña.
- Roles separados para clientes, profesionales y administración.
- Perfil profesional, verificación, catálogo de servicios, categorías e imágenes.
- Marketplace con búsqueda, filtros, favoritos, perfiles públicos y reseñas.
- Solicitudes de trabajo, cotizaciones estructuradas y negociación controlada.
- Pagos con Mercado Pago y comisión configurable del 15%.
- Flujo de trabajo completo: pago, llegada, inicio, finalización, confirmación y disputas.
- Matching on-demand por cercanía, categoría, disponibilidad y ubicación fresca.
- Modo inmediato y modo programado con fecha y bloque horario.
- Notificaciones internas, panel administrativo, moderación y auditoría.
- PWA instalable, página offline, encabezados de seguridad y endpoint `/health`.

## Flujo inmediato

Cuando el cliente necesita ayuda ahora, Chambapp busca profesionales disponibles en radios progresivos de 5, 10, 15 y 25 km. El cálculo de distancia se realiza en backend con Haversine y se valida nuevamente al aceptar una invitación.

El ciclo principal es:

```text
searching → matched → awaiting_quote → awaiting_payment → paid
→ on_the_way → arrived → in_progress → awaiting_confirmation → completed
```

También se contemplan solicitudes expiradas, canceladas y disputadas. No uso WebSockets, Redis, tracking GPS continuo ni procesos daemon para este flujo: el cliente y el profesional consultan el estado mediante polling protegido por rate limit.

## Stack

- Laravel 13
- PHP 8.3+
- MySQL
- Blade
- Bootstrap 5.3 y Bootstrap Icons
- Vite
- PHPUnit
- Mercado Pago Checkout Pro

## Requisitos

- PHP 8.3 o superior con extensiones estándar de Laravel.
- Composer.
- Node.js y npm para compilar los assets.
- MySQL.

## Instalación local

```bash
git clone https://github.com/roman00120/chambapp.git
cd chambapp
composer install
copy .env.example .env
php artisan key:generate
```

Configuro en `.env` la conexión MySQL y, si voy a probar pagos, las credenciales de Mercado Pago. Después ejecuto:

```bash
php artisan migrate
npm install
npm run build
php artisan storage:link
php artisan serve
```

La aplicación queda disponible normalmente en `http://127.0.0.1:8000`.

En Windows, el inicio de sesión con Google requiere que PHP tenga certificados
raíz configurados. El script local descarga el paquete oficial de curl.se la
primera vez y arranca PHP con esa configuración:

```powershell
.\serve-local.cmd
```

Aunque el servidor escucha en la red local, Google OAuth debe probarse desde
`http://127.0.0.1:8000`; Google no admite una IP privada como callback web.

## Variables importantes

```env
CHAMBAPP_PLATFORM_FEE_PERCENT=15
CHAMBAPP_PAYMENT_CURRENCY=MXN
CHAMBAPP_IMMEDIATE_TIMEOUT=5
CHAMBAPP_INVITATION_TIMEOUT=3
CHAMBAPP_LOCATION_FRESHNESS=30
```

En producción uso `APP_DEBUG=false`, HTTPS, cookies seguras, una base MySQL privada y credenciales de Mercado Pago del entorno correspondiente. Nunca subo `.env`, tokens, contraseñas ni datos de tarjetas.

## Calidad y pruebas

Para ejecutar la suite:

```bash
php artisan test
vendor/bin/pint --test
npm run build
```

La suite actual cubre autenticación, permisos, marketplace, cotizaciones, privacidad, pagos, comisión histórica, workflow, moderación, matching por cercanía, disponibilidad, expiración, búsqueda nueva, polling y modo programado.

## API REST v1 y futura app móvil

Chambapp conserva la web Blade + Bootstrap + sesiones y agrega una interfaz REST versionada en `/api/v1`. Ambas interfaces usan los mismos modelos, Policies y servicios de dominio (`OnDemandMatchingService`, `JobWorkflowService`, pagos, cálculo de comisión, reseñas y protección de contacto); la web no depende de la API.

La autenticación móvil usa Laravel Sanctum mediante `Authorization: Bearer TOKEN`. Los endpoints principales permiten registro/login, revocación del token actual o de todos los dispositivos, categorías, servicios, perfiles públicos, favoritos, disponibilidad, solicitudes inmediatas y programadas, matching, invitaciones, cotizaciones, checkout, pagos, workflow, reseñas y notificaciones. Los roles públicos admitidos al registrar son exclusivamente `client` y `professional`.

El contrato completo está en [docs/openapi.yaml](docs/openapi.yaml). Comprobación mínima:

```bash
curl http://127.0.0.1:8000/api/v1/health
curl http://127.0.0.1:8000/api/v1/categories
```

Las colecciones grandes están paginadas y las fechas usan ISO 8601. Los importes se entregan como cadenas decimales. Teléfono, email, dirección y coordenadas exactas no se serializan públicamente; los participantes sólo obtienen la ubicación privada después de un pago aprobado. El dispositivo nunca decide monto, comisión, profesional ganador, estado financiero ni transiciones críticas.

Al suspender/bloquear una cuenta o restablecer su contraseña se revocan todos sus tokens API. Cerrar sesión en la web no revoca tokens móviles; `/api/v1/auth/logout` revoca el dispositivo actual y `/api/v1/auth/logout-all` revoca todos los dispositivos.

Para ejecutar únicamente las pruebas API:

```bash
php artisan test tests/Feature/Api/V1
```

Una futura app Flutter, React Native, Kotlin o Swift podrá guardar el token en almacenamiento seguro y consumir este contrato. Ninguna APK debe contener `APP_KEY`, credenciales de base de datos, secretos de Mercado Pago ni secretos de webhook.

## Documentación adicional

- [Despliegue](DEPLOYMENT.md)
- [Operaciones](OPERATIONS.md)
- [Smoke test](SMOKE_TEST.md)

## Estado del proyecto

Estoy desarrollando Chambapp por fases. La base funcional incluye el marketplace, la contratación protegida, los pagos, la administración y el flujo on-demand. Las siguientes mejoras deben mantener las mismas reglas de privacidad, seguridad, trazabilidad financiera y autorización por rol.
