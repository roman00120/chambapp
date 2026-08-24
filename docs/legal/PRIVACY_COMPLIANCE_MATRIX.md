# Matriz de cumplimiento de privacidad

| Tratamiento futuro | Datos | Responsable/proveedor | Conservación | Riesgo y controles pendientes |
|---|---|---|---|---|
| Verificación de identidad profesional | documento oficial, selfie/liveness y resultado, según proveedor aún no seleccionado | proveedor KYC como encargado/subencargados por definir; Chambapp conserva resultado mínimo | definir por contrato y evaluación legal; borrar imágenes/biometría del proveedor según plazo aprobado | alto: consentimiento expreso para biometría, aviso, transferencias, ARCO, cifrado, apelación, revisión humana, incidentes y prueba de supresión |

La revisión interna de perfil, correo/teléfono, identidad y credenciales profesionales deben registrarse y mostrarse por separado.

> **BORRADOR PARA REVISIÓN POR ESPECIALISTA EN PRIVACIDAD MEXICANA.**

| Tratamiento | Datos / origen | Finalidad | Almacenamiento | Acceso / tercero | Conservación propuesta | Riesgo / medida / documento |
|---|---|---|---|---|---|---|
| Registro/autenticación | nombre, correo, teléfono, contraseña; persona/Google | cuenta y seguridad | MySQL/Hostinger | titular/admin; Google opcional | cuenta + bloqueo legal | A; aviso previo y aceptación; avisos/Términos |
| Sesiones/API | token, IP, agente; dispositivo/backend | autenticación/fraude | DB/secure storage móvil | titular/backend/Hostinger | sesión/token + logs `[definir]` | A; revocación/rotación; aviso integral |
| Perfil profesional | foto, bio, experiencia, ciudad, reputación; profesional/sistema | publicación/contratación | DB y storage público | público parcial/admin | cuenta + evidencia | A; visibilidad y alcance “verificado”; Términos profesionales |
| Ubicación cliente | dirección/coordenadas; cliente/GPS | solicitud/ejecución | DB | partes según estado/admin | operación + plazo breve/prescripción | A; minimización/roles; avisos y seguridad |
| Ubicación profesional | coordenadas recientes, radio, timestamp; GPS | matching inmediato | DB | algoritmo/profesional/admin | ubicación reciente con TTL corto `[definir]` | C; caducidad/política algorítmica; avisos/Términos prof. |
| Fotos/archivos | imagen/metadatos; cámara/archivo | describir/evidencia | storage local/público según tipo | partes/moderación | operación/disputa + `[definir]` | A; EXIF/acceso/borrado; contenido/privacidad |
| Pagos | montos, IDs, estado, tokens OAuth cifrados; backend/MP | cobro/conciliación | MySQL cifrado parcial | pagos/admin/MP | fiscal/contractual `[dictamen]` | C; consentimiento/base/DPA; pagos/aviso |
| Mensajes/notificaciones | contenido/destino; usuarios/sistema | coordinar/avisar | MySQL | partes/admin limitado | operación/disputa + `[definir]` | A; acceso/retención; comunidad/aviso |
| Reseñas/reportes | rating, comentario, nombre abreviado; cliente/reportante | reputación/moderación | MySQL | público parcial/admin | vigencia perfil + evidencia | M; apelación/retención; política de reseñas |
| Disputas | motivo/evidencia/conducta; partes/sistema | resolver/defender | MySQL/storage | partes limitado/admin/autoridad | prescripción/bloqueo | A; segregación y revisión humana; disputas/aviso |
| Admin/auditoría | actor, acción, IP, metadata; sistema | seguridad/evidencia | MySQL/logs | administradores autorizados | `[plazo de auditoría]` | A; inmutabilidad/acceso; aviso/programa seguridad |

## Controles legales

| Obligación LFPDPPP | Situación | Brecha |
|---|---|---|
| Principios de licitud, finalidad, lealtad, consentimiento, calidad, proporcionalidad, información y responsabilidad | Controles técnicos parciales | Falta programa documentado |
| Aviso integral y simplificado | Páginas MVP incompletas | Bloqueador |
| Consentimiento financiero/patrimonial | No se evidenció registro separado | Alto |
| Consentimiento sensible | No se pretende captar; puede aparecer en contenido | Procedimiento faltante |
| ARCO y responsable interno | No se observó canal/proceso | Bloqueador |
| Seguridad administrativa/técnica/física | Hay HTTPS, roles y cifrado parcial | Falta evaluación y evidencia integral |
| Incidentes | No se verificó playbook | Alto |
| Encargados/transferencias | Terceros identificables | Contratos/inventario pendientes |
| Bloqueo/supresión | Sin ciclo de vida completo | Alto |
| Decisiones automatizadas | Matching presente | Transparencia y oposición/revisión faltantes |

## Clasificación

La ubicación no está enumerada automáticamente como dato sensible en la LFPDPPP, pero es dato personal de alto riesgo por permitir inferencias y acceso físico. Montos y pagos son financieros/patrimoniales. Fotos, mensajes y disputas pueden contener incidentalmente datos sensibles; deben minimizarse.
