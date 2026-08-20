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

## Documentación adicional

- [Despliegue](DEPLOYMENT.md)
- [Operaciones](OPERATIONS.md)
- [Smoke test](SMOKE_TEST.md)

## Estado del proyecto

Estoy desarrollando Chambapp por fases. La base funcional incluye el marketplace, la contratación protegida, los pagos, la administración y el flujo on-demand. Las siguientes mejoras deben mantener las mismas reglas de privacidad, seguridad, trazabilidad financiera y autorización por rol.
