# Certificación preproducción Mercado Pago — Chambapp

Fecha: 2026-08-26. Alcance: repositorio web local, inspección SSH de producción y documentación oficial. No se realizaron cobros, preferencias, reembolsos, migraciones ni despliegues.

## A–F. Configuración, credenciales, OAuth, PKCE, Split y Checkout

| Área | Estado | Evidencia / riesgo |
|---|---|---|
| Configuración activa | VERIFICADO | Producción: `APP_ENV=production`, `APP_DEBUG=false`, URL, callback y webhook exactos; MXN y 15/15 activos. |
| Credenciales | APROBADO CON ADVERTENCIA | Las cinco están presentes. Access token y User ID fueron validados por `/users/me`; Client ID/Secret por `client_credentials`. Webhook secret está presente pero no puede validarse sin firma legítima/panel. |
| OAuth | CORREGIDO LOCALMENTE | `state` aleatorio, unido a sesión/usuario, single-use y con expiración configurable de 10 minutos; pendiente de deploy. |
| PKCE | NO VERIFICADO | No hay `code_challenge`/`code_verifier` en producción. Es bloqueante si PKCE está habilitado en el panel. |
| Split 1:1 | NO VERIFICADO | Código usa token seller y `marketplace_fee`; requiere confirmación de producto, KYC y elegibilidad en el panel. |
| Checkout Pro | APROBADO CON ADVERTENCIA | Preferencia usa cantidad 1, MXN, total 1150 y fee 300 para base 1000; no se creó preferencia real. |

## G–O. Economía, snapshots, webhook, conciliación y disputas

- El cálculo oficial usa enteros de centavos; no floats. Para base 1000 resulta exactamente 150 cliente, total 1150, comisión profesional 150, fee bruto 300 y profesional 850 antes de costos externos.
- Migración de snapshots 15/15 está aplicada en producción. Payment toma el snapshot del Job; la suite cubre preferencias y desajustes de monto, moneda, referencia, collector y split.
- La firma de webhook se verifica antes de efectos. Una solicitud sin firma al endpoint productivo recibió 401. El controlador vuelve a consultar Mercado Pago server-side antes de conciliar.
- Hay locks, índices únicos de IDs de proveedor y transacciones de auditoría; la suite cubre duplicados, regresiones de estado, refunds parciales/totales y chargebacks.
- `provider_fee` se mantiene separado. No se certifica una política legal automática de refunds/chargebacks.

## P–W. Métodos, seguridad, logs, concurrencia, red, DB, Flutter y Web

- Métodos de pago: **REQUIERE CONFIRMACIÓN** en cuenta de prueba/panel; no se asumen tarjeta, saldo, transferencia, OXXO o SPEI.
- Secretos: no se imprimieron. Existe `.env.backup.mercadopago.20260825T051752Z` no rastreado en producción, modo 0600, con dos variables Mercado Pago. Riesgo: secreto persistente dentro del release.
- Producción tiene árbol Git sucio (numerosos cambios y archivos sin seguimiento), incluido el backup anterior. Riesgo: no existe artefacto reproducible ni rollback fiable. No se alteró.
- Red: GET de consulta/concilación ahora tiene retry finito exponencial con jitter y respeta `Retry-After` para 429. POST de preferencias y OAuth se envía una sola vez porque Preferences no documenta idempotency key; un resultado ambiguo se reporta sin crear duplicados.
- Flutter/móvil: NO VERIFICADO; el repositorio móvil no fue puesto a disposición en este workspace.
- UI: revisión de código disponible, no prueba visual productiva autenticada.

## X. Pruebas ejecutadas

- `php artisan test`: **240 tests, 1,464 assertions, 0 failures** (local).
- `PaymentIntegrationTest`: **26 tests, 162 assertions, 0 failures** tras añadir prueba adversarial OAuth expirada.
- No se ejecutó `php artisan test` en producción para proteger datos productivos.
- Flutter: no ejecutado, repositorio no disponible.

## Defecto corregido localmente

**Antes:** OAuth guardaba state y usuario en sesión sin `issued_at`; un state abandonado era válido mientras viviera la sesión.

**Causa raíz:** faltaba una vida útil server-side para el state.

**Corrección:** `issued_at` y límite configurable de 600 s (mínimo 60 s) en `ProfessionalPaymentController`.

**Prueba:** `test_expired_mercado_pago_oauth_state_is_rejected_without_exchanging_the_code` confirma rechazo y cero llamadas OAuth.

**Riesgo y plan deploy:** desplegar sólo desde commit limpio, ejecutar pruebas, revisar panel PKCE, y hacer smoke test sin pago. No desplegado durante esta auditoría.

## Y–Z. Riesgos restantes y plan E2E controlado

Bloqueadores antes de E2E:

1. Eliminar de forma autorizada y segura el backup `.env` y confirmar que no quedó en backups/CI; considerar rotación si se expuso fuera de permisos 0600.
2. Reconstruir producción desde un commit limpio; no desplegar el árbol modificado actual.
3. Desplegar la expiración OAuth local con su test.
4. Desplegar y observar el retry/backoff finito ya probado para lecturas seguras.
5. Confirmar en panel Split 1:1, KYC, Redirect URL, eventos, secreto y PKCE. Si PKCE está activado, implementar PKCE antes de cualquier prueba.
6. Probar Flutter desde su repositorio válido.

Procedimiento E2E futuro (no ejecutar aún): crear cuentas oficiales de prueba marketplace/seller/buyer; conectar seller por OAuth; cotizar 1000.00; comprobar resumen 1000 + 150 = 1150 y fee 300; crear preferencia de prueba; aprobar sólo con método permitido por Mercado Pago; verificar webhook firmado, consulta server-side, Job pagado, dashboard y split. Confirmar reversión/refund únicamente si la documentación de cuentas de prueba lo permite.

## Dictamen

MERCADO PAGO NO APTO PARA PRUEBA E2E CONTROLADA
