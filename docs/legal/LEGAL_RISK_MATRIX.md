# Matriz de riesgos legales

| Riesgo adicional | Nivel | Mitigación pendiente |
|---|---:|---|
| Biometría/documentos con proveedor KYC | Crítico | evaluación legal y de impacto, consentimiento expreso, contrato de encargado, transferencias/subencargados, retención/supresión, seguridad e incidentes |
| Falso positivo/negativo o rechazo automático | Alto | reintento proporcional, revisión humana, explicación, corrección y apelación auditada |
| Insignia engañosa | Alto | mostrar “Identidad verificada” sólo con evidencia vigente; explicar alcance y excluir garantías/licencias/antecedentes |

> **BORRADOR PARA REVISIÓN POR ABOGADO MEXICANO. NO CONSTITUYE ASESORÍA LEGAL.**

| Riesgo | Nivel | Evidencia/causa | Acción antes de lanzamiento | Responsable sugerido |
|---|---|---|---|---|
| Clasificación laboral de profesionales | Crítico | El modo “Ahora” asigna invitaciones por algoritmo, ubicación, disponibilidad, estado y radio; hay calificaciones, estados de cuenta y comisión. LFT 291-A a 291-U puede aplicar aunque el contrato diga “independiente”. | Dictamen laboral; modelar costos IMSS/INFONAVIT/prestaciones; contrato laboral de plataforma y política algorítmica si aplica. | Abogado laboral + dirección |
| Identidad jurídica del operador | Crítico | Faltan razón social/nombre, RFC y domicilio comprobable. | Definir operador y completar todos los textos, facturación, Mercado Pago y avisos. | Dirección + fiscal |
| Retenciones fiscales de plataforma | Crítico | Chambapp cobra/intermedia pagos y retiene comisión; SAT contempla “otro tipo de servicios”. | Dictamen LISR 113-A–C/LIVA 18-J–M; alta de obligaciones, CFDI/retenciones e informativas. | Contador fiscalista |
| Reembolsos y cancelación | Alto | Código cancela sólo ciertos estados; disputa no reembolsa; webhook registra reembolsos externos, pero no existe política operativa completa. | Aprobar matriz por estado/causa, SLA, responsable y mecanismo de devolución Mercado Pago. | Legal + operaciones + pagos |
| Información al consumidor | Alto | Páginas legales actuales son marcadores MVP; falta precio total/comisión, garantías, contacto, cancelación y comprobantes de manera integral. | Publicar textos aprobados y mostrar costo total antes de aceptar/pagar. | Legal + producto |
| Aviso y consentimiento de privacidad | Alto | Se tratan identidad, contacto, ubicación, pagos y conducta; no existe evidencia de aceptación/versionado. | Designar área ARCO; inventario; avisos integral/simplificado; registro de versión/fecha/medio. | Privacidad + producto |
| Ubicación precisa | Alto | Solicitudes guardan dirección/coordenadas; profesionales guardan ubicación reciente al estar disponibles. | Minimización, caducidad, controles de acceso, explicación clara, borrado/bloqueo y evaluación de riesgo. | Privacidad + seguridad |
| Datos financieros/patrimoniales | Alto | Montos, pagos, comisiones, identificadores y cuenta Mercado Pago; la ley exige consentimiento expreso salvo excepción. | Determinar base legal por finalidad y obtener consentimiento expreso donde corresponda. | Privacidad + pagos |
| Menores | Alto | No se observa control de edad. Servicios a domicilio y pagos elevan riesgo. | Decidir 18+; validación razonable; prohibir cuentas/trabajo de menores; protocolo de detección. | Legal + producto |
| Seguridad física en domicilios | Alto | Profesionales acuden a direcciones de clientes. | Verificación proporcional, botón/protocolo de emergencia, reporte, seguros y categorías prohibidas. | Operaciones + seguros |
| Responsabilidad por servicios | Alto | Chambapp selecciona candidatos, muestra “verificado”, cobra y modera. Exclusiones absolutas serían vulnerables. | Definir alcance real de verificación, garantías y cooperación; evitar promesas engañosas. | Legal + operaciones |
| Evidencia electrónica | Medio-alto | Aceptación, cotización, pagos y estados son electrónicos; no se versionan términos aceptados. | Registro de versión, timestamp, usuario/IP/dispositivo y conservación conforme Código de Comercio/NOM-151. | Legal + ingeniería |
| Transferencias/encargados | Medio-alto | Hostinger, Google y Mercado Pago tratan datos; pueden existir transferencias internacionales. | Contratos/DPA, inventario de encargados, ubicación, finalidades y cláusulas de transferencia. | Privacidad + compras |
| Reseñas y contenido | Medio | Comentarios públicos, reportes y moderación; prueba técnica conserva texto potencialmente ofensivo en datos. | Reglas claras, notice-and-action, privacidad, evidencia y apelación. | Confianza y seguridad |
| Derechos de autor e imagen | Medio | Fotos de servicios, perfiles y solicitudes. | Licencia de contenido, declaraciones de titularidad, retiro y conservación de evidencia. | Legal + moderación |
| Cookies | Medio | Cookies esenciales de sesión/XSRF; no se detectó analítica publicitaria. | Política esencial; consentimiento granular sólo si después se incorporan cookies no necesarias. | Privacidad + web |
| RC/ciberseguro | Medio-alto | Daños en domicilio, errores profesionales y brechas. | Cotizar RC general/tecnológica, cyber, crimen/fraude y cobertura por tarea/categoría. | Seguros + legal |
| Licencias por categoría | Variable/alto | Electricidad, gas, construcción, salud u otras actividades pueden tener reglas locales/sectoriales. | Matriz por categoría y entidad; bloquear categorías sin requisitos definidos. | Legal regulatorio |

## Orden de atención

1. **Go/no-go:** operador, laboral, fiscal y pagos/reembolsos.
2. **Antes de captar usuarios:** privacidad, menores, seguridad y aceptación electrónica.
3. **Antes de escalar categorías:** licencias, seguros, verificación y moderación.

## Registro detallado exigido

Abreviaturas: **C** crítico, **A** alto, **M** medio, **B** bajo. P = probabilidad; I = impacto.

| Riesgo | Nivel / P / I | Fundamento principal | Mitigación contractual | Mitigación técnica | Mitigación operativa | Abogado |
|---|---|---|---|---|---|---|
| Consumidor / plataforma | A / alta / alta | LFPC 7, 7 Bis, 76 Bis, 85 | Rol real, precio total, remedios; sin exención absoluta | resumen de compra, evidencia y comprobante | soporte y conciliación | Sí |
| Profesional causa daño/lesión | A / media / crítica | responsabilidad civil y LFPC según hechos | deber de cuidado, licencias, indemnidad válida sin desplazar derechos | verificación y evidencia | seguros y protocolo de siniestro | Sí |
| Daños/robo en domicilio | A / media / crítica | civil/penal y deberes propios | reglas de acceso y cooperación | identidad, reportes, controles de dirección | RC, emergencia, investigación | Sí |
| Accidente del profesional | C / media / crítica | LFT 291-A–U, LSS | reconocer derechos aplicables | tiempo efectivo y evento de accidente | IMSS/seguro y atención | Sí obligatorio |
| Fraude/suplantación | A / media / alta | LFPC, LFPDPPP, penal | información veraz y medidas proporcionales | MFA/alertas/limitación | KYC proporcional y playbook | Sí |
| No-show profesional | A / media / alta | LFPC 7, 92 Bis/Ter | reembolso/bonificación clara | estados y evidencia | SLA y reemplazo | Sí |
| No-show cliente | M / media / media | contrato/LFPC | cargo sólo proporcional e informado | check-in y ventana | revisión humana | Sí |
| Trabajo mal hecho/incompleto | A / alta / alta | LFPC y civil | alcance, garantía/remedios | fotos/confirmación sin renuncia | peritaje/escalamiento | Sí |
| Cancelaciones | A / alta / alta | LFPC/contrato | matriz por estado y causa | autoservicio y registro | SLA | Sí |
| Reembolsos | A / media / alta | LFPC/contrato/PSP | total/parcial y plazos | API/conciliación idempotente | fondo/responsable | Sí |
| Precio/comisión oculta | A / media / alta | LFPC 7 Bis | desglose antes de aceptar | cálculo servidor/UI | auditoría de cargos | Sí |
| Pago MP/split | A / media / alta | LFPC, fiscal, contrato PSP | roles y términos de tercero | webhook verificado | conciliación diaria | Sí |
| Contracargos | A / media / alta | reglas PSP/consumo | procedimiento y evidencia | estados sin doble abono | defensa y reservas | Sí |
| Disputas | A / alta / alta | LFPC/civil | proceso no excluyente | expediente y revisión humana | mediación/SLA | Sí |
| Impuestos/facturación | C / alta / crítica | LISR 113-A–C; LIVA; CFF | asignar obligaciones conforme ley | RFC/CFDI/reportes | contador y enteros | Sí fiscal |
| Relación laboral | C / alta / crítica | LFT 291-A–U | contrato correcto, no simulación | algoritmo/tiempo/recibos | IMSS/INFONAVIT | Sí obligatorio |
| Geolocalización/domicilios | A / alta / alta | LFPDPPP 5, 12, 18 | aviso/finalidad y confidencialidad | minimización/TTL/roles | auditoría/acceso | Sí privacidad |
| Fotografías/imagen | M / media / alta | LFPDPPP/LFDA/civil | licencia y autorizaciones | EXIF, acceso y borrado | retiro/evidencia | Sí |
| Filtración | C / media / crítica | LFPDPPP 18–20, sanciones | deberes con encargados | cifrado, MFA, monitoreo | respuesta/notificación | Sí |
| Eliminación/retención | A / alta / alta | LFPDPPP 10, 24–34 | explicar bloqueo/excepciones | ciclo de vida/baja | tabla de plazos | Sí |
| Menores | A / media / crítica | civil, laboral y derechos de NNA | 18+ y prohibiciones | control de edad | escalamiento/protección | Sí |
| Reseña falsa/difamación | M / media / alta | civil/penal/LFDA/LFPDPPP | autenticidad y reglas | sólo trabajo, reportes | moderación/apelación | Sí |
| Contenido/PI | M / media / media | LFDA/marcas | licencia limitada y garantías | notice-and-action | agente de reclamos | Sí |
| Seguridad física | C / media / crítica | civil/laboral/sectorial | obligaciones realistas | SOS/reportes sólo si se implementan | protocolo y seguros | Sí |
| Aceptación electrónica | A / alta / alta | Código de Comercio 89 ss.; NOM-151 | texto/versiones/cambios | aceptación, hash y timestamp | custodia/aprobaciones | Sí |
| Publicidad “verificado/seguro” | A / media / alta | LFPC publicidad e información | definir alcance, no garantías absolutas | mostrar fecha/alcance | revisión periódica | Sí |
| Doble cargo de plataforma (15% cliente + 15% profesional) | A / alta / crítica | LFPC, fiscal, reglas PSP | revelar ambos cargos antes de consentir; no llamarlos impuestos ni ganancia neta | snapshots servidor y desglose por actor | dictamen fiscal, reembolso y conciliación | Sí |
| Diferencia entre estimado profesional y liquidación PSP | A / alta / alta | LFPC, contractual, reglas Mercado Pago | aclarar costos externos, impuestos y retenciones | provider_fee y liquidación conciliada separados | soporte y conciliación manual | Sí |
