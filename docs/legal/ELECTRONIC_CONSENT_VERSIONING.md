# Consentimiento electrónico, versionado y usuarios existentes

> **DISEÑO PROPUESTO; NO IMPLEMENTADO NI AUTORIZADO.**

## Interfaz posterior

- Casillas no premarcadas, separadas por documento jurídicamente distinto.
- Enlaces accesibles antes de aceptar; texto legible en móvil y web.
- Acción afirmativa separada para datos financieros/sensibles cuando proceda.
- El profesional acepta términos profesionales y, si aplica, contrato/política laboral por flujo separado.
- No condicionar finalidades secundarias innecesarias al uso esencial.

## Modelo propuesto

`legal_documents`:

- `id`, `document_type`, `version`, `title`, `content_hash`, `effective_at`, `published_at`, `is_material`, `storage_path`.

`legal_acceptances`:

- `id`, `user_id`, `legal_document_id`, `accepted_at`, `platform` (`web`, `android`, `ios`), `ip_address`, `user_agent` limitado, `evidence_hash`.

Aplicar minimización y controles de acceso. La IP/agente se conservan sólo si la finalidad probatoria y plazo son aprobados. El hash no reemplaza conservar de forma íntegra el documento presentado.

## Versiones

- Inicial: Términos 1.0, Privacidad 1.0, Profesionales 1.0, una vez aprobados; estos números no implican aceptación histórica.
- Cambio material: precio/comisión, responsabilidades, datos/finalidades/transferencias, disputas, laboral o derechos. Requiere aviso y nueva aceptación cuando corresponda.
- Cambio no material: ortografía/contacto sin reducir derechos. Registrar versión; informar sin bloquear salvo criterio legal.
- Conservar histórico, fechas, redline, aprobación y prueba de despliegue.

## Usuarios existentes

No marcarlos automáticamente como aceptados. Al siguiente acceso, mostrar resumen del cambio, documentos completos y acción afirmativa; permitir descargar/consultar. Para tratamiento indispensable previo, documentar base y ofrecer baja/ARCO. Si la persona no acepta términos necesarios, limitar nuevas operaciones de forma proporcional sin borrar obligaciones/derechos previos.

## Evidencia

Registrar exactamente qué texto se mostró, fecha/hora del servidor, identidad autenticada, canal y resultado. Considerar sellado/conservación conforme Código de Comercio y NOM-151 con asesoría especializada.

