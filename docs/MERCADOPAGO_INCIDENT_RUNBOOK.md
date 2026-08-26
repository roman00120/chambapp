# Runbook de incidentes — Mercado Pago

No ejecutar cobros, refunds, migraciones ni borrados como parte de este runbook sin autorización operacional.

## Mercado Pago caído, 429 o 5xx

1. No marcar pagos como aprobados ni crear preferencias adicionales automáticamente.
2. Registrar de forma sanitizada `payment_id`, `job_id`, código HTTP y correlación; nunca tokens, secretos ni headers Authorization.
3. Suspender reintentos manuales repetidos; aplicar backoff finito después de desplegarlo.
4. Conciliar pagos pendientes mediante consulta server-side por `external_reference` cuando el proveedor se recupere.

## Webhooks ausentes o fallando

1. Revisar métricas de 401, 5xx, firmas inválidas y pagos no conciliados.
2. Verificar en el panel URL, eventos y secreto sin copiar secretos a tickets.
3. Ejecutar el comando de conciliación solamente en una ventana autorizada y en modo seguro para pendientes; conservar evidencia de discrepancias.
4. No usar el retorno del navegador para aprobar trabajos.

## OAuth deja de funcionar o refresh falla

1. Marcar el seller como requiriendo reconexión; no borrar tokens ni registros históricos.
2. Verificar Client ID, Client Secret, Redirect URL y configuración PKCE en el panel.
3. No reasignar un seller a otro profesional. La vinculación se mantiene única.

## Deploy rompe checkout o cache conserva configuración vieja

1. Detener nuevos checkouts desde la aplicación.
2. Capturar commit, `git status`, versiones y estado de cache antes de actuar.
3. Desplegar sólo un artefacto Git limpio y revisado; no editar archivos de aplicación en producción.
4. Verificar rutas, `APP_ENV=production`, `APP_DEBUG=false`, credenciales por presencia y prueba de firma inválida antes de reabrir.

## Marketplace deshabilitado o discrepancia de split

1. Mantener el Job fuera de estado pagado y abrir incidente de conciliación.
2. Conservar `payment_id`, `external_reference`, collector, importe, moneda y fee reportado.
3. Confirmar habilitación, KYC y condiciones comerciales con Mercado Pago. No compensar ni reembolsar automáticamente.
