# Investigación de proveedores de verificación de identidad

Fecha de revisión: 23 de agosto de 2026. Alcance limitado a documentación oficial; no se abrió cuenta, contrató servicio ni integró proveedor.

## 1. Incode — recomendación preliminar

- México/INE: documentación específica para validación gubernamental mexicana. Puede comparar selfie con imagen de INE cuando se habilita validación facial.
- Integración: API, flujos móviles/web y webhooks.
- Prueba de vida: disponible como módulo dentro del flujo; debe confirmarse el paquete contratado.
- Datos: la configuración contractual de conservación, región y eliminación debe validarse con ventas/privacidad.
- Precio: cotización; no se encontró tarifa pública aplicable al flujo mexicano.
- Fuentes oficiales: https://developer.incode.com/reference/processgovernmentvalidation y https://developer.incode.com/docs/system-of-record-mexico

**Motivo de recomendación:** ofrece el ajuste documental más directo para México/INE y un flujo API utilizable desde Laravel con captura móvil. La recomendación es técnica y preliminar; depende de contrato, precio, residencia de datos, subencargados, soporte, precisión y revisión jurídica.

## 2. Veriff

- México/INE: solución específica de verificación biométrica contra el registro INE; exige consentimiento INE explícito y configuración/registro adicionales.
- Integración: API y decision webhook. La solución INE descrita es sólo API y requiere que el integrador capture la selfie.
- Seguridad: endpoints HMAC y validación de firma para webhooks.
- Precio: cotización para la solución INE; no se encontró tarifa pública aplicable.
- Fuentes oficiales: https://devdocs.veriff.com/v1/docs/ine-biometric-database-verification y https://devdocs.veriff.com/docs/webhooks-guide

## 3. Persona

- Cobertura: documento gubernamental, selfie y prueba de vida; la evidencia pública consultada no confirma un cotejo directo con INE equivalente a Incode/Veriff.
- Integración: API, SDKs y webhooks; herramientas de retención/redacción dependen del plan.
- Precio: plan Essential publicado desde USD 250/mes con compromiso anual; extras, volumen y funciones avanzadas deben cotizarse.
- Fuentes oficiales: https://withpersona.com/pricing, https://withpersona.com/product/verifications/government-id/ y https://docs.withpersona.com/webhooks

## Criterios obligatorios antes de decidir

Prueba controlada con documentos mexicanos; tasa de falsos positivos/negativos; accesibilidad; recuperación y apelación; vigencia/reverificación; SLA; contrato de encargado; ubicación y transferencias; subprocesadores; cifrado; borrado verificable; respuesta a incidentes; consentimiento biométrico; costo total; soporte en español; firmas de webhook e idempotencia.

No se deben colocar claves de ningún proveedor en Flutter. Laravel debe crear sesiones, verificar webhooks y conservar sólo estados/referencias mínimas. La bandera `PROFESSIONAL_IDENTITY_VERIFICATION_REQUIRED` debe permanecer en `false` hasta completar estas decisiones y la integración real.
