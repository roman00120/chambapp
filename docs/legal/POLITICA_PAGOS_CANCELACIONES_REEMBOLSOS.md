# Política de pagos, cancelaciones y reembolsos

> **BORRADOR. REQUIERE APROBACIÓN LEGAL, FISCAL Y OPERATIVA.** No autoriza cobros ni reembolsos automáticos.

## Principios

1. Mostrar monto total en MXN, impuestos, cargos y comisión antes de confirmar.
2. Calcular importes en Laravel; no confiar en montos enviados por la app.
3. Procesar pagos con Mercado Pago y verificar el estado con el proveedor.
4. No confundir una disputa interna con un reembolso ejecutado.
5. No limitar bonificaciones o derechos mínimos de la LFPC.

## Flujo observado

- Profesional emite cotización; cliente la acepta; el trabajo pasa a pendiente de pago.
- Laravel crea una preferencia de Mercado Pago.
- El webhook valida y consulta el pago; el estado aprobado habilita el trabajo.
- La comisión se calcula en backend. El código actual usa 15%, pendiente de decisión fiscal/legal.
- Cancelación técnica sólo está disponible antes de ciertos estados; la disputa se abre al terminar y **no genera devolución por sí misma**.

## Matriz propuesta para decisión

| Momento | Regla propuesta | Decisión pendiente |
|---|---|---|
| Antes de aceptar cotización | Cancelación sin cargo | Confirmar |
| Cotización aceptada, sin pago | Cancelación sin cargo | Confirmar expiración/reserva |
| Pagado, profesional no inicia | Reembolso total, salvo costos legalmente trasladables e informados | SLA y responsable |
| Profesional en camino/llegó | Evaluación de costos reales; nunca penalidad desproporcionada | Fórmula y evidencia |
| Trabajo iniciado | Reembolso parcial/total según avance, incumplimiento y evidencia | Autoridad de decisión |
| Servicio no prestado/deficiente por causa del proveedor | Reembolso/bonificación conforme LFPC, sin limitar indemnización | Procedimiento |
| Fraude, duplicado o contracargo | Congelar conciliación; investigar y evitar doble devolución | Playbook |
| Disputa | No alterar pago automáticamente; preservar evidencia y resolver con SLA | SLA `[PENDIENTE]` |

## Procedimiento mínimo

La solicitud debe registrar operación, motivo y evidencia. Chambapp acusará recibo por `[CANAL]`, resolverá en `[PLAZO]`, comunicará fundamento y, si procede, ordenará devolución al medio original. Se informará el plazo estimado del procesador. No se cobrará por presentar una reclamación.

## Mercado Pago

La URL exacta observada del webhook es `https://chambapp.com.mx/webhooks/mercadopago`. Los retornos deben usar HTTPS y rutas reales generadas por el backend. El webhook no constituye por sí mismo política de consumo; sólo aporta evidencia del proveedor.

## Facturación

Definir quién emite CFDI por el servicio y por la comisión, y quién entrega constancias de retención. Pendiente de dictamen conforme LISR/LIVA y reglas SAT de plataformas tecnológicas.

## Prohibiciones

- Cobros o comisiones ocultas.
- Cláusulas de “no reembolso” absolutas.
- Hacer depender derechos de una calificación o renuncia.
- Reembolsar sin verificar identidad/operación o duplicar devoluciones.
- Guardar credenciales privadas de Mercado Pago en cliente móvil, documentación o repositorio.

## Modelo económico aprobado para operaciones nuevas

- Precio base: lo cotiza el profesional.
- Cargo al cliente: 15% del precio base, revelado antes del pago.
- Comisión al profesional: 15% del precio base, revelada al profesional.
- Total cliente: precio base más cargo al cliente.
- Ingreso bruto de plataforma: suma del cargo cliente y la comisión profesional; no es ganancia neta.
- Monto estimado profesional: precio base menos comisión profesional, antes de impuestos, retenciones y costos externos.

La comisión de Mercado Pago no se confunde con ninguno de los dos cargos de Chambapp. Esta política no crea una regla automática nueva para reembolsar o conservar esos cargos: esa decisión sigue pendiente de revisión jurídica y operativa.
