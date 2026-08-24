# Auditoría jurídica del producto real

> **BORRADOR TÉCNICO-JURÍDICO.** Estado observado en repositorios al 23-08-2026; no equivale a prueba de operación humana o producción.

## Matriz de 30 puntos

| # | Función | Estado | Evidencia principal | Hallazgo |
|---:|---|---|---|---|
| 1 | Registro cliente | Implementado | `RegisterUserRequest`, `UserRegistrationService`, `AuthController`; `POST /api/v1/auth/register`; `AuthApiTest` | Nombre, email, teléfono, contraseña y rol cliente. |
| 2 | Registro profesional | Implementado | mismos archivos; `ProfessionalProfile` | Crea usuario profesional y perfil; perfil inicia pendiente/no verificado. |
| 3 | Información solicitada | Implementado | requests de registro/perfil/servicio | Registro básico; después bio, experiencia, ubicación, foto y servicios. |
| 4 | Datos almacenados | Implementado | migraciones `users`, `professional_profiles`, `job_requests`, pagos, mensajes, reseñas, disputas | Incluye contacto, ubicación, contenido, reputación, pagos, sesiones e IP. |
| 5 | Geolocalización | Implementado | `UpdateProfessionalLocationRequest`, `ProfessionalProfile`; Flutter `geolocator_location_service.dart`; AndroidManifest | Ubicación a petición para trabajo/disponibilidad; no se observó rastreo continuo en segundo plano. |
| 6 | On-Demand | Implementado | `OnDemandMatchingService`, rutas `/ahora`, API de jobs inmediatos; `JobApiTest` | Búsqueda con caducidad, radios y rondas. |
| 7 | Matching | Implementado | `OnDemandMatchingService::inviteCandidates/acceptInvitation` | Categoría, disponibilidad, ubicación fresca, radio, estado activo y carga; gana primera aceptación válida. |
| 8 | Crear solicitud | Implementado | `JobRequestService`; requests programado/inmediato; `POST /api/v1/jobs/scheduled|immediate` | Guarda descripción, ubicación, fecha y fotos cuando aplica. |
| 9 | Seleccionar profesional | Implementado | servicio elegido/programado; matching inmediato | Programado se vincula al servicio/profesional; inmediato por aceptación de invitación. |
| 10 | Cotización | Implementado | `JobWorkflowService::createQuote`; rutas job quotes; `JobApiTest` | Profesional envía monto/descripcion; expira en 48 horas. |
| 11 | Quién determina precio | Implementado | `createQuote`, `PaymentCalculationService` | Profesional propone; backend normaliza/calcula; plataforma conserva reglas económicas. |
| 12 | Aceptación cliente | Implementado | `acceptQuote`; `POST .../quotes/{quote}/accept` | Aceptación invalida otras cotizaciones y fija `agreed_price`. |
| 13 | Obligación/momento de pago | Implementado parcialmente | `acceptQuote` lleva a `awaiting_payment`; `PaymentService` exige cotización aceptada | Técnicamente el pago es paso previo al trabajo; falta definición contractual de formación/obligación y cancelación. |
| 14 | Mercado Pago | Implementado | `MercadoPagoService`, `PaymentService`, `MercadoPagoWebhookController` | Checkout Pro/preferencias, consulta del proveedor y webhook. |
| 15 | Comisión | Implementado | `PaymentCalculationService`, `config/chambapp.php`; tests M6/payment | Backend calcula 15% configurado; requiere ratificación fiscal/comercial. |
| 16 | Quién recibe dinero | Implementado con condición | `MercadoPagoService`, `ProfessionalProfile` OAuth, `marketplace_fee` | En jobs, preferencia usa token del vendedor conectado y fee de plataforma; confirmar contrato/settlement real MP. |
| 17 | Split Payment | Implementado | `marketplace_fee` en `MercadoPagoService`; integridad en `PaymentService` | Modelo marketplace, no custodia bancaria propia observada. |
| 18 | Conexión MP profesional | Implementado | controlador OAuth profesional, `ProfessionalProfile`, settings view | Tokens OAuth cifrados; conexión y renovación según configuración. |
| 19 | Cancelaciones | Implementado parcialmente | `JobWorkflowService::cancel`, `OnDemandMatchingService::cancelSearch` | Sólo estados previos concretos; no cubre matriz posterior al pago/no-show. |
| 20 | Reembolsos | Implementado parcialmente | `PaymentService`/`CommerceService` concilian `refunded_amount/refunded_at`; tests de hardening | Detecta/registre reembolso del proveedor; no se observó flujo completo iniciado por usuario/admin. |
| 21 | Contracargos | Implementado parcialmente | mapeo y reconciliación de webhook en servicios de pago | Registra estados y evita regresiones; falta playbook de fondos, evidencia, notificación y apelación. |
| 22 | Disputas | Implementado parcialmente | `JobWorkflowService::openDispute`, `JobDispute`, admin controllers, `M6ApiTest` | Cliente abre al esperar confirmación; admin resuelve estado sin modificar pago. |
| 23 | Trabajo terminado | Implementado | `finish` y `confirmCompletion` | Profesional marca término; cliente confirma con código de 6 dígitos vigente 24 h. |
| 24 | Reseñas | Implementado | `ReviewService`, `ReviewResource`, `ReviewController`, `M7ContractApiTest` | Una por trabajo completado, 1–5, pública con nombre abreviado, reporte/moderación. |
| 25 | Suspensión de cuentas | Implementado | admin user/status controllers, `UserStatus`, middleware/auth checks, `AdminControlPanelTest` | Admin cambia estado; falta política de causa, aviso y apelación. |
| 26 | Google OAuth | Implementado | `GoogleAuthController`; API `AuthController::google`; Flutter `google_identity_provider.dart`; `AuthApiTest` | Web usa OAuth; móvil envía ID token y backend valida audiencia. |
| 27 | Tokens | Implementado | Sanctum; Flutter `SecureSessionStorage`; casts cifrados MP en `ProfessionalProfile` | Token móvil en secure storage; tokens MP sólo servidor. Falta política de retención/rotación integral. |
| 28 | Datos compartidos entre partes | Implementado | `JobResource`, `UserResource`, `ProfessionalResource`; `JobApiTest` | Datos de operación, cotización y perfil; contacto/dirección exacta se restringen según actor/estado. |
| 29 | Momento de compartición | Implementado | lógica de recursos y políticas; pruebas de privacidad de jobs | Oculta antes del estado habilitante/pago y revela a participantes cuando se requiere ejecutar. |
| 30 | Información pública | Implementado | `ProfessionalPublicProfileController`, `ProfessionalResource`, `PublicApiTest` | Nombre, foto/avatar, bio, ciudad/estado, experiencia, servicios, verificación y reputación; no email/teléfono/coordenadas. |

## Diferencias frente a la descripción del negocio

- La comisión 15% sí está configurada y calculada, pero no equivale por sí sola a una política fiscal aprobada.
- Reembolsos/contracargos tienen conciliación defensiva, no un procedimiento completo de atención al consumidor.
- “Verificado” existe como estado, pero el código no define por sí solo qué documentos/antecedentes humanos se revisan.
- No se observó borrado integral de cuenta ni exportación ARCO; sólo eliminación de recursos como servicios/favoritos.
- No se observó analítica publicitaria, píxeles ni rastreo de ubicación continuo.
- Las páginas `/terminos` y `/privacidad` son marcadores MVP, no documentos listos para operar, y contienen mojibake.

## Mercado Pago: caracterización

El cliente inicia checkout desde Chambapp; Laravel crea la preferencia con Mercado Pago. Para trabajos, el token OAuth del profesional conectado permite actuar sobre su cuenta y se envía `marketplace_fee`; Mercado Pago procesa y notifica. Chambapp valida firma, consulta el pago y concilia monto, moneda, referencia, collector/entorno y estados. Esto respalda una caracterización de **marketplace con proveedor de pagos externo**, no banco ni depositario. La caracterización jurídica final depende de contratos y flujo real de liquidación.

## Actualización del modelo económico

La decisión comercial V2 establece dos cargos separados sobre el precio base: 15% al cliente y 15% al profesional. El backend conserva snapshots del precio base, ambos porcentajes, ambos cargos, total cliente, ingreso bruto de plataforma y monto profesional anterior a costos externos. Mercado Pago documenta que su propia comisión se descuenta de los fondos del vendedor antes de la comisión del marketplace; por ello $850 en el ejemplo de $1,000 es una cifra anterior a costos externos, no la liquidación garantizada. Persisten riesgos fiscales, laborales, de reembolsos y contracargos que requieren dictamen.
