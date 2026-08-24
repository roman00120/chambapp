# Lista de implementación legal — no ejecutar sin autorización

> Este documento es una especificación, no una autorización para modificar software o producción.

## Bloqueadores

- [ ] Aprobar dictamen laboral y modelo operativo.
- [ ] Aprobar dictamen fiscal y facturación/retenciones.
- [ ] Completar identidad y contactos del operador.
- [ ] Aprobar los documentos por abogado mexicano.
- [ ] Aprobar reglas económicas, cancelación, reembolso y disputas.

## Web y app

- [ ] Sustituir los marcadores legales MVP, conservando UTF-8 correcto.
- [ ] Mostrar aviso simplificado antes o al recabar datos y enlazar integral.
- [ ] En registro: enlaces visibles y aceptación separada/versionada de términos y privacidad.
- [ ] Para profesionales: aceptación separada de términos profesionales y, si aplica, contrato laboral/política algorítmica.
- [ ] Guardar versión, fecha/hora, usuario, canal y evidencia de aceptación; no usar casillas premarcadas.
- [ ] Mostrar precio total, impuestos, comisión/cargos y política aplicable antes de confirmar.
- [ ] Entregar comprobante de operación y acceso posterior al historial.
- [ ] Incorporar autoservicio para baja/cancelación cuando sea legalmente procedente.
- [ ] Crear canal ARCO y procedimiento humano verificable.
- [ ] Crear centro de seguridad, denuncias, apelaciones y emergencia.

## Mapa por componente

| Componente | Implementación posterior requerida |
|---|---|
| Web | páginas públicas aprobadas, cookies, descarga/impresión, UTF-8 y accesibilidad |
| API | documentos/versiones, aceptación, consulta, revocación/baja y controles ARCO |
| Flutter | enlaces visibles, aceptación no premarcada, reaceptación y permisos contextualizados |
| Administración | publicar versiones, consultar evidencia, resolver ARCO/disputas/apelaciones y auditoría |
| Registro | avisos antes de recabar, edad, casillas separadas y evidencia |
| Perfil | privacidad/visibilidad, baja, exportación, licencias y alcance de verificación |
| Pagos | desglose total, política aplicable, comprobante, cancelación y reembolso conciliado |
| Footer/menú | operador, contacto, términos, privacidad, cookies, pagos, seguridad y PROFECO |

## Datos y seguridad

- [ ] Inventario de datos/encargados/transferencias y plazos de conservación.
- [ ] Evaluación de riesgo para ubicación y matching automatizado.
- [ ] Minimizar coordenadas históricas y fotos de domicilios.
- [ ] Documentar controles, accesos, respaldos, incidentes y notificación.
- [ ] Verificar contratos con Hostinger, Mercado Pago y Google.
- [ ] Prohibir secretos y documentos de identidad en logs/repositorios.

## Operación

- [ ] Capacitar soporte y moderación; elaborar playbooks y SLA.
- [ ] Verificar profesionales conforme a promesas publicadas.
- [ ] Matriz de licencias y seguros por categoría/estado.
- [ ] Canal PROFECO y conciliación; evaluar Concilianet/RCAL.
- [ ] Flujo contable de reembolsos, contracargos, comisiones y CFDI.
- [ ] Revisión trimestral y ante cada cambio material.

## Evidencia de salida

- [ ] Acta/aprobación de legal, fiscal, laboral, privacidad, seguridad y dirección.
- [ ] Pruebas de texto y enlaces en web/app.
- [ ] Pruebas de aceptación, cancelación, ARCO, reembolso y exportación.
- [ ] Copia inmutable de cada versión publicada y fecha de vigencia.
