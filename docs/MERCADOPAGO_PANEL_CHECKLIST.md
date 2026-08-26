# Checklist manual de panel Mercado Pago

Realiza una acción por vez y responde con el texto/estado visible, sin capturar ni pegar secretos.

## Acción 1 — PKCE

Ruta: **Mercado Pago > Tus integraciones > aplicación Chambapp > Detalles de aplicación > Editar > OAuth / Authorization Code > PKCE**.

Reporta exactamente uno: `PKCE deshabilitado`, `PKCE habilitado` o `el panel no muestra esta opción`. Si está habilitado, Mercado Pago exige `code_challenge` y `code_method`; Chambapp no debe desplegarse hasta implementar PKCE S256. [Documentación oficial](https://www.mercadopago.com.mx/developers/es/docs/split-payments/additional-content/security/oauth/creation)

## Acciones posteriores (no ejecutar hasta cerrar la anterior)

| Área | Ruta | Valor esperado |
|---|---|---|
| Callback OAuth | Tus integraciones > Chambapp > Detalles de aplicación > Redirect URL | `https://chambapp.com.mx/profesional/pagos/configuracion/oauth/callback` |
| Webhook | Tus integraciones > Chambapp > Webhooks > Configurar notificaciones > Modo productivo | `https://chambapp.com.mx/webhooks/mercadopago` |
| Eventos | Misma pantalla > Eventos | `Payments` únicamente para el código actual. Activar `Chargebacks` sólo después de que el controlador soporte ese tópico explícitamente; no marcar todos. |
| Firma | Misma pantalla | Confirmar que el secreto mostrado corresponde a ese endpoint; no copiarlo. Usar `Simular` oficial con un Data ID no financiero y registrar sólo HTTP/resultados. |
| Marketplace/Split | Tus integraciones > Chambapp > Detalles/Editar | Pagos online + Checkout Pro + modelo Marketplace/Split 1:1. |
| Seller KYC | Cada cuenta seller > Perfil/Verificación | KYC nivel 6 y OAuth autorizado; confirmar con evidencia no sensible. |

La documentación de Checkout Pro indica configurar el evento **Payments** para creaciones y cambios de estado; las notificaciones se simulan desde el panel sin un pago real. [Webhooks oficiales](https://www.mercadopago.com.mx/developers/es/docs/checkout-pro/payment-notifications)
