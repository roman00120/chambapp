# Didit KYC (borrador técnico)

Esta integración usa Hosted Sessions de Didit API V3. Laravel es la única fuente de verdad del estado KYC y Flutter/web sólo consultan o solicitan iniciar una sesión.

## Contratos oficiales utilizados

- Crear sesión: `POST https://verification.didit.me/v3/session/`
- Consultar decisión: `GET https://verification.didit.me/v3/session/{session_id}/decision/`
- Autenticación server-side: encabezado `x-api-key`
- Webhook V3: `POST https://chambapp.com.mx/webhooks/didit`
- Eventos suscritos: `status.updated`, `data.updated`
- Firma preferida: `X-Signature-V2`; alternativas verificadas: `X-Signature` y `X-Signature-Simple`
- Protección replay: `X-Timestamp` dentro de una ventana máxima de 300 segundos
- Idempotencia: `event_id` único y creación de sesión estable por `(workflow_id, vendor_data)`

Referencias oficiales:

- https://docs.didit.me/sessions-api/create-session
- https://docs.didit.me/sessions-api/retrieve-session
- https://docs.didit.me/integration/webhooks
- https://docs.didit.me/sessions-api/management-api

## Configuración

Las variables sólo viven en el servidor:

```dotenv
DIDIT_API_URL=https://verification.didit.me
DIDIT_API_KEY=
DIDIT_WORKFLOW_ID=
DIDIT_WEBHOOK_SECRET=
DIDIT_TIMEOUT=10
PROFESSIONAL_IDENTITY_VERIFICATION_PROVIDER=didit
PROFESSIONAL_IDENTITY_VERIFICATION_REQUIRED=false
```

`.env.example` contiene únicamente nombres y valores públicos o vacíos. No se deben introducir secretos en Git, Flutter, JavaScript o documentación.

## Minimización

Chambapp no persiste imágenes de documentos, selfies, videos, plantillas biométricas ni el cuerpo completo de la decisión. Sólo conserva estado, referencia de sesión, fechas, códigos seguros, consentimiento, hash del payload del webhook e historial mínimo.

El workflow publicado incluye OCR de documento, liveness y face match. Esto no demuestra ni se presenta como una consulta contra una base gubernamental específica del INE.

## Estados

| Didit | Chambapp |
|---|---|
| Approved | verified |
| Declined | rejected |
| In Review | needs_review |
| Expired / Kyc Expired | expired |
| Abandoned | rejected (reintento permitido) |
| Not Started / In Progress / Awaiting User / Resubmitted | pending |

Un callback del navegador, parámetro de consulta, payload de Flutter o webhook sin firma jamás puede conceder `verified`. Después de un webhook válido, Laravel vuelve a consultar la Decision API.
