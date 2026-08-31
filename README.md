# Chambapp 🛠️🇲🇽

Chambapp es un marketplace de servicios y oficios para México que conecta directamente a clientes con profesionales verificados para resolver trabajos de mantenimiento, hogar, reparaciones y servicios especializados.

La plataforma opera bajo el modelo de **Marketplace Directo (Zero Radar)** con custodia de pagos mediante **Mercado Pago Checkout Pro** y comisión del 15%. La dirección exacta y los datos de contacto permanecen protegidos hasta que el pago queda formalmente aprobado.

---

## 🚀 Arquitectura del Producto

### 1. Modelo Marketplace Directo (Zero Radar)
- **Catálogo Directo:** El cliente explora servicios publicados por profesionales, consulta perfiles públicos, reseñas y contrata directamente.
- **Chamba Ahora (Inmediata):** Selección directa de categoría y profesional disponible, avanzando directamente al checkout sin subastas ni tiempos de espera.
- **Programar:** Agenda citas seleccionando categoría, profesional/servicio, fecha y franja horaria (`08:00-11:00`, `11:00-14:00`, `14:00-17:00`, `17:00-20:00`).
- **Sin Subastas ni Rondas:** Cero matching radial, cero temporizadores de radar y cero invitaciones masivas en las contrataciones nuevas.

### 2. Flujo Operativo y Ciclo de Vida del Trabajo
```text
awaiting_payment ──(Pago Mercado Pago)──> paid ──(En camino)──> on_the_way
  ──(Llegada)──> arrived ──(Iniciar)──> in_progress ──(Marcar terminado)──> awaiting_confirmation
  ──(Código OTP 6 dígitos)──> completed ──(Liberación de fondos + Reseña)
```

---

## 🛠️ Stack Tecnológico

- **Backend:** Laravel 13 (PHP 8.3+)
- **Base de Datos:** MySQL 8
- **Frontend Web:** Blade, Vanilla CSS / Bootstrap 5.3, Vite
- **Mobile:** Flutter 3.24+ (Dart 3.x) con Riverpod, GoRouter, Dio
- **Pagos:** Mercado Pago SDK / REST API con Webhook HMAC-SHA256
- **Colas / Workers:** Driver `database` con cron worker en producción
- **Suite de Pruebas:** PHPUnit 12 (Backend) + Flutter Test (Mobile)

---

## 💻 Instalación y Configuración desde Cero (Nueva Computadora)

### Requisitos Previos
- **PHP:** 8.3 o superior con extensiones (`pdo_mysql`, `curl`, `mbstring`, `openssl`, `intl`).
- **Composer:** 2.x
- **Node.js & npm:** Node 20+ y npm 10+
- **MySQL:** Servidor MySQL 8.0+
- **Flutter SDK:** 3.24+ (para el cliente móvil)
- **Android SDK / Studio:** Build Tools 34+ y JBR/JDK 17+.

---

### 1. ⚙️ Puesta en Marcha de Backend y Web

```bash
# 1. Clonar el repositorio
git clone https://github.com/roman00120/chambapp.git
cd chambapp

# 2. Instalar dependencias PHP
composer install

# 3. Crear archivo de variables de entorno
cp .env.example .env

# 4. Generar clave de cifrado de la aplicación
php artisan key:generate

# 5. Configurar conexión MySQL en .env y correr migraciones
php artisan migrate --seed

# 6. Crear symlink para archivos públicos
php artisan storage:link

# 7. Instalar dependencias npm y compilar assets
npm install
npm run build

# 8. Iniciar el servidor local
php artisan serve
# La aplicación queda disponible en: http://127.0.0.1:8000
```

---

### 2. 📱 Puesta en Marcha de Mobile (Flutter)

```bash
# 1. Clonar el repositorio móvil
git clone https://github.com/roman00120/chambapp-mobile.git
cd chambapp-mobile

# 2. Instalar dependencias de Flutter
flutter pub get

# 3. Ejecutar en Emulador Android (conectando al backend local)
flutter run \
  --dart-define=APP_ENV=development \
  --dart-define=API_BASE_URL=http://10.0.2.2:8000/api/v1

# 4. Ejecutar en Dispositivo Físico (LAN Wi-Fi)
flutter run \
  --dart-define=APP_ENV=development \
  --dart-define=API_BASE_URL=http://TU_IP_LOCAL:8000/api/v1

# 5. Ejecutar conectado al Backend de Producción
flutter run \
  --dart-define=APP_ENV=production \
  --dart-define=API_BASE_URL=https://chambapp.com.mx/api/v1
```

---

### 3. 🧪 Ejecución de Tests

```bash
# Backend (PHPUnit)
cd chambapp
php artisan test
# o: ./vendor/bin/phpunit

# Mobile (Flutter Analyze & Tests)
cd chambapp-mobile
flutter analyze
flutter test
```

---

## 🔒 Variables de Entorno Clave (`.env`)

| Variable | Descripción / Valor Recomendado |
| :--- | :--- |
| `APP_NAME` | `Chambapp` |
| `APP_ENV` | `local` en desarrollo / `production` en producción |
| `APP_DEBUG` | `true` en local / `false` obligatorio en producción |
| `APP_URL` | `http://localhost:8000` (local) o `https://chambapp.com.mx` (prod) |
| `QUEUE_CONNECTION` | `database` (para ejecución asíncrona con worker) |
| `CHAMBAPP_PLATFORM_FEE_PERCENT` | `15` (Comisión del 15% para la plataforma) |
| `CHAMBAPP_PAYMENT_CURRENCY` | `MXN` |
| `MERCADOPAGO_ACCESS_TOKEN` | Credencial de acceso a la pasarela de pagos |
| `MERCADOPAGO_WEBHOOK_SECRET` | Secreto HMAC para validar webhooks entrantes |
| `GOOGLE_CLIENT_ID` / `_SECRET` | Credenciales de Google OAuth |

> [!CAUTION]
> Nunca incluyas credenciales reales, tokens o contraseñas en Git. Utiliza siempre `.env.example` como plantilla.

---

## 🔑 Compilación de APK Release Firmada

Para compilar la APK de producción oficial:
1. Copia tu keystore privado a:
   `~/.chambapp/signing/chambapp-upload.jks`
2. Configura las variables de entorno de firma:
   ```powershell
   $env:JAVA_HOME = "C:\Program Files\Android\Android Studio\jbr"
   $env:PATH = "$env:JAVA_HOME\bin;$env:PATH"
   $env:CHAMBAPP_KEYSTORE_PATH = "$env:USERPROFILE\.chambapp\signing\chambapp-upload.jks"
   $env:CHAMBAPP_KEY_ALIAS = "chambapp-upload"
   $env:CHAMBAPP_STORE_PASSWORD = "<PASSWORD_DEL_KEYSTORE>"
   $env:CHAMBAPP_KEY_PASSWORD = "<PASSWORD_DE_LA_LLAVE>"
   ```
3. Ejecuta la compilación:
   ```bash
   flutter build apk --release \
     --dart-define=APP_ENV=production \
     --dart-define=API_BASE_URL=https://chambapp.com.mx/api/v1 \
     --dart-define=GOOGLE_SERVER_CLIENT_ID=750372864737-skigrd07vk2l3ivv50k2mrgp4u2taahb.apps.googleusercontent.com
   ```

---

## 🌐 Arquitectura de Despliegue en Producción (Hostinger)

- **Estructura de Directorios:**
  - Releases: `/home/u291776795/releases/chambapp-<commit>`
  - DocumentRoot: `/home/u291776795/domains/chambapp.com.mx/public_html` con symlink hacia `build`
  - Storage Compartido: `/home/u291776795/shared/chambapp/storage` (persiste fotos y avatars)
- **Queue Worker:** `/home/u291776795/queue_worker.php` ejecutado por Cron cada minuto para procesar correos y notificaciones en segundo plano.

---

## 📄 Licencia

Desarrollado por **Roman Velasco Moctezuma**. Todos los derechos reservados. Proyecto privado.
