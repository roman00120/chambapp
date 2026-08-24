# Paquete jurídico preliminar de Chambapp — México

> **BORRADOR PARA REVISIÓN POR ABOGADO MEXICANO. NO CONSTITUYE ASESORÍA LEGAL.**

Fecha de corte de la investigación: **23 de agosto de 2026**.

Este directorio contiene una auditoría documental basada en el código web/API y móvil disponible en los repositorios de Chambapp. No sustituye la revisión del operador real, su situación fiscal, la operación diaria, contratos con proveedores ni la configuración efectiva de terceros.

## Conclusión ejecutiva

Chambapp puede describirse comercialmente como un **marketplace digital que conecta clientes con profesionales**, facilita cotizaciones, contratación, comunicación, reputación y pagos. No debe afirmar que queda libre de toda responsabilidad por llamarse “intermediario”. La Ley Federal de Protección al Consumidor, la normativa de datos, las obligaciones fiscales y, especialmente, el capítulo de trabajo en plataformas digitales pueden asignarle obligaciones directas.

**No se recomienda lanzamiento comercial** hasta cerrar, al menos:

1. identidad, RFC, domicilio y canales oficiales del operador;
2. dictamen laboral sobre los artículos 291-A a 291-U de la Ley Federal del Trabajo y su aplicación al modo “Ahora”;
3. dictamen fiscal de retenciones, CFDI e IVA/ISR para plataforma intermediaria;
4. política real de cancelación, reembolso, garantía y resolución de disputas;
5. responsable/canal ARCO, inventario de encargados y plazos de conservación;
6. aceptación demostrable y versionada de términos y avisos en web y app;
7. reemplazo de las páginas legales MVP actuales, que son marcadores incompletos y contienen texto mal codificado;
8. cobertura de seguros y verificación/licencias por categoría de servicio.

## Documentos

- [Matriz de riesgos](LEGAL_RISK_MATRIX.md)
- [Términos generales](TERMINOS_Y_CONDICIONES.md)
- [Términos para profesionales](TERMINOS_PROFESIONALES.md)
- [Pagos, cancelaciones y reembolsos](POLITICA_PAGOS_CANCELACIONES_REEMBOLSOS.md)
- [Aviso de privacidad integral](AVISO_PRIVACIDAD_INTEGRAL.md)
- [Aviso de privacidad simplificado](AVISO_PRIVACIDAD_SIMPLIFICADO.md)
- [Política de cookies](POLITICA_COOKIES.md)
- [Reseñas y contenido](POLITICA_RESEÑAS_CONTENIDO.md)
- [Comunidad y seguridad](POLITICA_COMUNIDAD_SEGURIDAD.md)
- [Disputas](POLITICA_DISPUTAS.md)
- [Cumplimiento PROFECO](PROFECO_COMPLIANCE.md)
- [Matriz de privacidad](PRIVACY_COMPLIANCE_MATRIX.md)
- [Riesgo laboral](INDEPENDENT_CONTRACTOR_RISK.md)
- [Información faltante](LEGAL_MISSING_INFORMATION.md)
- [Lista de implementación](LEGAL_IMPLEMENTATION_CHECKLIST.md)
- [Abogados a considerar](LEGAL_COUNSEL_SHORTLIST.md)
- [Brief para abogado](LAWYER_REVIEW_BRIEF.md)
- [Seguros recomendados](INSURANCE_RECOMMENDATIONS.md)
- [Fuentes oficiales](FUENTES_OFICIALES.md)

## Alcance técnico observado

- Registro por correo/contraseña y Google; roles cliente, profesional y administrador.
- Tokens móviles Sanctum y sesión web; IP y agente de usuario en sesiones.
- Perfiles, servicios, fotos, ciudad/estado, experiencia, reputación y verificación.
- Ubicación exacta para solicitudes y ubicación reciente de profesionales disponibles.
- Solicitudes programadas e inmediatas, asignación por radio, invitaciones, cotizaciones y flujo de estados.
- Pagos con Mercado Pago, comisión configurada de plataforma, OAuth de vendedores y webhooks.
- Mensajes, notificaciones, favoritos, reseñas, reportes, disputas y bitácora administrativa.
- Protección parcial de datos: datos de contacto/dirección se ocultan públicamente y se revelan a participantes según el estado.

## Limitaciones

No se verificaron documentos corporativos, alta fiscal, contratos de Hostinger/Mercado Pago/Google, procedimientos humanos, contabilidad, pólizas, licencias estatales/municipales ni la configuración viva de producción. Los textos usan marcadores hasta que el operador entregue esos datos.

