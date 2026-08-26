# Production reconciliation report

Fecha: 2026-08-26. Producción inspeccionada: `/home/u291776795/chambapp`, HEAD `24e8ef6f8a878bbb3434525a7659cf763888ca95`. GitHub comparado: `origin/master` en `e1f89c1a41492a98f24c1928cca5fa550084307f`.

No se desplegó código, no se ejecutaron migraciones y no se modificaron la base de datos, credenciales, lógica Mercado Pago ni porcentajes. El working tree productivo permaneció intacto durante el inventario y la comparación.

## Rescate verificado

Antes del `git fetch`, se creó `/home/u291776795/deploy-backups/reconcile-20260826T160543Z` fuera del repositorio y del document root, modo `0700`. Todos sus archivos son modo `0600`. Contiene snapshot de código, patch binario completo, inventarios, metadatos, bundle Git, `.env` productivo, backup Mercado Pago y archivo de logs separado. `SHA256SUMS` permite verificar integridad. El paquete local de análisis excluye `.env`, backups sensibles y logs.

## Resultado ejecutivo

- 53 archivos productivos de código son idénticos al HEAD actual de GitHub.
- 12 archivos productivos que difieren del HEAD actual están preservados byte por byte en `0357fd26` o `de12a40f`; GitHub contiene evoluciones posteriores, no pérdidas.
- `public/images/chambapp-logo.png` es idéntico en producción y GitHub.
- Las 216 vistas compiladas eliminadas son cache generado y ya no están rastreadas en GitHub.
- `storage/logs/laravel.log` y los fixtures runtime ya fueron retirados del tracking en GitHub.
- `.env.backup.mercadopago.20260825T051752Z` y `error_log` no son código y no se integran.
- No se encontró código legítimo exclusivo de producción que requiera copiarse.

## Inventario de código y assets

`Idéntico` significa hash SHA-256 igual al archivo de `origin/master`. `Histórico exacto` significa que el blob productivo existe byte por byte en el commit indicado y el HEAD actual contiene una evolución posterior.

| Archivo | Clasificación | Estado GitHub | Acción propuesta |
|---|---|---|---|
| `app/Console/Commands/ProductionPreflight.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Enums/IdentityVerificationStatus.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Enums/ProfessionalCredentialStatus.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Exceptions/IdentityVerificationRequiredException.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Http/Controllers/Admin/CommissionController.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Http/Controllers/Admin/ProfessionalController.php` | A/B código legítimo | Histórico exacto en `de12a40f`; HEAD ampliado | Conservar versión GitHub |
| `app/Http/Controllers/Api/V1/AdminController.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Http/Controllers/Api/V1/AdminOperationsController.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Http/Controllers/Api/V1/AuthController.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Http/Controllers/Api/V1/JobController.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Http/Controllers/Api/V1/ProfessionalController.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Http/Controllers/Api/V1/ProfessionalIdentityVerificationController.php` | A/B código legítimo | Histórico exacto en `de12a40f`; HEAD integra Didit | Conservar versión GitHub |
| `app/Http/Controllers/Api/V1/PublicController.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Http/Controllers/ClientJobRequestController.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Http/Controllers/ClientOnDemandController.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Http/Controllers/Dashboard/ProfessionalDashboardController.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Http/Controllers/HomeController.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Http/Controllers/MercadoPagoWebhookController.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Http/Controllers/PaymentController.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Http/Controllers/Professional/ProfessionalIdentityVerificationController.php` | A/B código legítimo | Histórico exacto en `de12a40f`; HEAD integra Didit | Conservar versión GitHub |
| `app/Http/Controllers/ProfessionalPublicProfileController.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Http/Controllers/PublicServiceController.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Http/Resources/Api/V1/JobQuoteResource.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Http/Resources/Api/V1/JobRequestResource.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Http/Resources/Api/V1/PaymentResource.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Http/Resources/Api/V1/ProfessionalIdentityVerificationResource.php` | A/B código legítimo | Histórico exacto en `de12a40f`; HEAD ampliado | Conservar versión GitHub |
| `app/Http/Resources/Api/V1/ProfessionalResource.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Http/Resources/Api/V1/UserResource.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Models/JobRequest.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Models/Payment.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Models/ProfessionalCredential.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Models/ProfessionalIdentityVerification.php` | A/B código legítimo | Histórico exacto en `de12a40f`; HEAD ampliado | Conservar versión GitHub |
| `app/Models/ProfessionalProfile.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Models/User.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Services/JobWorkflowService.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Services/MercadoPagoService.php` | A/B código legítimo | Histórico exacto en `de12a40f`; HEAD añade retry/backoff seguro | Conservar versión GitHub |
| `app/Services/OnDemandMatchingService.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Services/PaymentCalculationService.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Services/PaymentService.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Services/ProfessionalAvailabilityService.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Services/ProfessionalIdentityVerificationService.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Services/ServiceSearchService.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/Services/UserRegistrationService.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `app/ValueObjects/PaymentCalculation.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `bootstrap/app.php` | A/B código legítimo | Histórico exacto en `0357fd26`; HEAD añade webhook Didit | Conservar versión GitHub |
| `config/chambapp.php` | A/B código legítimo | Histórico exacto en `de12a40f`; HEAD añade controles OAuth/retry/consentimiento | Conservar versión GitHub |
| `database/migrations/2026_08_23_000001_add_dual_fee_economic_snapshots.php` | A/B migración legítima | Idéntica | Usar GitHub; no ejecutar aún |
| `database/migrations/2026_08_24_000001_create_professional_identity_verifications.php` | A/B migración legítima | Idéntica | Usar GitHub; no ejecutar aún |
| `public/images/chambapp-logo.png` | H asset legítimo | Idéntico | Mantener versionado en GitHub |
| `resources/views/admin/commissions/index.blade.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `resources/views/admin/dashboard.blade.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `resources/views/admin/payments/index.blade.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `resources/views/admin/payments/show.blade.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `resources/views/admin/professionals/show.blade.php` | A/B código legítimo | Histórico exacto en `de12a40f`; HEAD amplía auditoría Didit | Conservar versión GitHub |
| `resources/views/components/professional-card.blade.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `resources/views/components/service-card.blade.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `resources/views/components/ui/badge.blade.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `resources/views/components/ui/brand-mark.blade.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `resources/views/dashboards/professional.blade.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `resources/views/jobs/show.blade.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `resources/views/payments/earnings.blade.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `resources/views/payments/summary.blade.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `resources/views/professional/identity-verification/show.blade.php` | A/B código legítimo | Histórico exacto en `de12a40f`; HEAD integra Hosted Session/consentimiento | Conservar versión GitHub |
| `resources/views/professional/profile/public.blade.php` | A/B código legítimo | Idéntico | Usar GitHub |
| `routes/api.php` | A/B código legítimo | Histórico exacto en `de12a40f`; HEAD añade endpoints Didit | Conservar versión GitHub |
| `routes/web.php` | A/B código legítimo | Histórico exacto en `de12a40f`; HEAD añade flujo Didit | Conservar versión GitHub |

## Runtime, secretos y generados

| Archivo/grupo | Clasificación | Estado GitHub | Acción propuesta |
|---|---|---|---|
| 216 archivos `storage/framework/views/*.php` eliminados | D/E/J runtime, cache, generado | Ya retirados del tracking | No rescatar como fuente; regenerar en deploy futuro |
| `storage/framework/testing/disks/public/services/*` | D/I fixture runtime | Ya retirado del tracking | Mantener sólo `.gitignore` |
| `storage/logs/laravel.log` | F log | Ya retirado del tracking e ignorado | Conservar sólo en backup operativo; nunca subir |
| `error_log` | F log | No existe en GitHub | Añadir `/error_log` a `.gitignore`; no copiar |
| `.env.backup.mercadopago.20260825T051752Z` | G backup/secreto | Ignorado y no versionado | Mover fuera del repo tras verificar respaldo; no rotar automáticamente |
| `.env` productivo | G secreto/config | No versionado | Mantener server-side; copia privada ya verificada |

## Didit y Mercado Pago

El código Didit completo existe en `origin/master`, incluida la migración `2026_08_25_000001_integrate_didit_identity_verification.php`. No se configuró ni desplegó. `PROFESSIONAL_IDENTITY_VERIFICATION_REQUIRED=false` permanece confirmado en producción.

La configuración Mercado Pago no se copió ni modificó. Las cinco variables siguen presentes y el modelo 15/15 no cambió. El HEAD de GitHub conserva la lógica productiva y añade expiración OAuth y retry/backoff únicamente para lecturas seguras.

## Verificación posterior

- Laravel: 240 tests, 1,464 assertions, 0 fallos.
- Flutter: 128 archivos conformes a formato, `flutter analyze` sin hallazgos y 126 tests aprobados.
- El backup `.env.backup.mercadopago.20260825T051752Z` fue comparado byte por byte con la copia del rescate y movido a `/home/u291776795/deploy-backups/secrets/mercadopago/`, fuera del repo y de `public`, modo `0600`.
- No se rotaron secretos. Las comprobaciones HTTPS previas de `/.env`, `/.git/config` y del nombre conocido del backup respondieron `403`; esto no demuestra exposición ni sustituye el retiro ya realizado.

## Dictamen de integración

No hay cambios legítimos únicos que deban copiarse desde producción. La futura reconciliación debe partir de `origin/master`, conservar sus evoluciones posteriores y desplegar mediante un release limpio después de una revisión separada. Este informe no autoriza limpiar ni reemplazar producción.
