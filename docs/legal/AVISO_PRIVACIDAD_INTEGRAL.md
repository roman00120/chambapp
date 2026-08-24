# Aviso de Privacidad Integral de Chambapp

> Nota de implementación: antes de activar verificación de identidad o biometría, incorporar y validar jurídicamente el alcance descrito en `IDENTITY_VERIFICATION_DRAFT.md`. La revisión de perfil, correo/teléfono, identidad y credenciales profesionales no deben presentarse como equivalentes.

> **BORRADOR PARA REVISIÓN POR ABOGADO MEXICANO. NO PUBLICAR CON MARCADORES.**

Versión `[VERSIÓN]` · Última actualización `[FECHA]`

## 1. Responsable

`[RAZON_SOCIAL_O_NOMBRE_PENDIENTE]`, RFC `[RFC_PENDIENTE]`, domicilio `[DOMICILIO_PENDIENTE]`, es responsable del tratamiento de datos realizado por Chambapp. Área de datos personales: `[ÁREA_PENDIENTE]`; contacto ARCO: `[CORREO_ARCO_PENDIENTE]`.

## 2. Datos tratados

- **Identidad y contacto:** nombre, correo, teléfono, rol, identificador de Google y foto cuando se usa ese acceso.
- **Cuenta y seguridad:** contraseña cifrada, tokens/sesiones, IP, agente de usuario, eventos de acceso, estado y bitácoras.
- **Perfil profesional:** biografía, experiencia, ciudad/estado/código postal, fotografía, servicios, disponibilidad, verificación, reputación y trabajos completados.
- **Ubicación:** dirección y coordenadas de solicitudes; coordenadas recientes, radio y fecha de actualización de profesionales disponibles.
- **Operación:** solicitudes, fotos, fechas, cotizaciones, estados, códigos de finalización, mensajes, notificaciones, favoritos, reseñas, reportes y disputas.
- **Financieros/patrimoniales:** montos, comisión, estado e identificadores de pagos, reembolsos/contracargos y conexión Mercado Pago. Los tokens OAuth de profesionales se almacenan cifrados. Chambapp no debe recibir números completos de tarjeta.
- **Contenido técnico/soporte:** comunicaciones, archivos y evidencia aportada.

No se pretende recabar datos sensibles. Si fotos, mensajes o disputas revelan salud, creencias u otros datos sensibles, se limitará su uso a la finalidad legítima y se obtendrá consentimiento expreso y por escrito cuando la ley lo requiera.

## 3. Finalidades primarias

- crear, autenticar, proteger y administrar cuentas;
- publicar perfiles/servicios con la información que se indique como pública;
- recibir solicitudes, localizar candidatos, asignar invitaciones y facilitar cotizaciones;
- revelar datos de contacto/ubicación sólo a participantes y en el momento operacional necesario;
- procesar y conciliar pagos, comisión, reembolsos y contracargos;
- permitir mensajes, notificaciones, reseñas, reportes, disputas y soporte;
- verificar profesionales y prevenir fraude/abuso;
- cumplir obligaciones legales, fiscales, laborales, seguridad y requerimientos de autoridad;
- conservar evidencia y defender derechos.

Las finalidades secundarias de publicidad personalizada, analítica no esencial o prospección **no se observaron como implementadas**. No deben añadirse sin actualizar este aviso y ofrecer mecanismos de negativa/consentimiento.

## 4. Base y consentimiento

El tratamiento se sustenta, según el caso, en consentimiento y/o en ser necesario para la relación jurídica y obligaciones legales. Datos financieros o patrimoniales requieren consentimiento expreso salvo excepción legal. La ubicación se solicita de forma visible al usar las funciones correspondientes; debe ser opcional cuando no sea imprescindible.

## 5. Datos públicos y revelación entre usuarios

Pueden mostrarse nombre, foto, biografía, ciudad/estado, experiencia, servicios y reputación profesional. El código observado excluye públicamente correo, teléfono, dirección y coordenadas exactas. Dirección/contacto pueden revelarse a las partes de una operación cuando el estado lo justifique. Las reseñas muestran una versión abreviada del nombre del cliente.

## 6. Encargados y transferencias

Se prevén, sujeto a contratos e inventario final:

- **Hostinger:** alojamiento, base de datos y entrega del servicio;
- **Mercado Pago:** pagos, prevención de fraude, OAuth y cumplimiento propio;
- **Google:** autenticación OAuth cuando la persona elige ese método;
- proveedores de correo/soporte/respaldos `[POR CONFIRMAR]`.

Los encargados tratarán datos conforme instrucciones y seguridad contratadas. Mercado Pago y Google pueden actuar también como responsables independientes bajo sus avisos. Las transferencias distintas de encargados se sujetarán a los artículos 35 y 36 de la LFPDPPP y a las excepciones legales. Países, subencargados y contratos están pendientes de inventario.

## 7. Conservación

Se conservarán datos mientras la cuenta/operación esté activa y después sólo por plazos fiscales, laborales, contractuales, prevención de fraude, disputas y prescripción. Tras cumplir la finalidad se bloquearán y suprimirán cuando proceda. La ley dispone eliminar datos relativos al incumplimiento contractual tras 72 meses. Deben aprobarse plazos concretos para: ubicación reciente, fotos, mensajes, tokens, pagos, auditoría, reseñas y disputas.

## 8. Seguridad e incidentes

Chambapp aplicará medidas administrativas, técnicas y físicas proporcionales: control por roles, cifrado de contraseñas/tokens sensibles, HTTPS, registro, respaldos, gestión de accesos y respuesta a incidentes. Las vulneraciones que afecten significativamente derechos patrimoniales o morales se comunicarán de forma inmediata a las personas afectadas con la información necesaria para actuar.

## 9. Derechos ARCO y revocación

La persona puede solicitar acceso, rectificación, cancelación u oposición; limitar uso/divulgación o revocar consentimiento sin efectos retroactivos. Enviar solicitud a `[CORREO_ARCO_PENDIENTE]` con:

1. nombre y medio para notificaciones;
2. acreditación de identidad/representación;
3. datos involucrados;
4. derecho solicitado y detalles útiles.

Se comunicará determinación en máximo 20 días hábiles y, si procede, se hará efectiva dentro de los 15 siguientes; los plazos pueden ampliarse una sola vez con justificación. Se designará `[PERSONA_O_DEPARTAMENTO_PENDIENTE]`. Una baja de cuenta no obliga a borrar información sujeta a bloqueo o conservación legal.

## 10. Decisiones automatizadas

El matching considera categoría, disponibilidad, ubicación reciente, radio, estado de cuenta y carga activa. Las personas pueden oponerse a tratamientos automatizados que produzcan efectos jurídicos no deseados o afecten significativamente sus derechos en los supuestos legales. Debe habilitarse explicación y revisión humana por `[CANAL_PENDIENTE]`.

## 11. Menores

Chambapp está dirigido a mayores de 18 años. Si se detectan datos de una persona menor sin base válida, se restringirán y se atenderá su eliminación/regularización, preservando evidencia necesaria para protección y autoridad.

## 12. Cookies y dispositivos

La web usa cookies esenciales de sesión y protección CSRF; véase la Política de Cookies. La app usa almacenamiento seguro para sesión y solicita ubicación mediante permisos Android al invocar funciones relacionadas.

## 13. Cambios y autoridad

Los cambios se comunicarán en `https://chambapp.com.mx/privacidad` y por `[MEDIO_ADICIONAL]` si son materiales. La autoridad definida por la LFPDPPP vigente es la Secretaría Anticorrupción y Buen Gobierno. Quedan a salvo los medios legales de protección.
