# Mercado Pago Split 1:1 — preparación de producción

Fecha de consulta: 2026-08-26.

## Evidencia oficial

- [Split 1:1 — requisitos previos](https://www.mercadopago.com.mx/developers/es/docs/split-payments/split-1-1/prerequisites): México está incluido; el seller requiere KYC 6, OAuth, credenciales y cuentas de prueba.
- [Crear configuración de Split 1:1](https://www.mercadopago.com.mx/developers/es/docs/split-payments/split-1-1/integration-configuration/create-configuration): la aplicación debe crearse como Pagos online, Checkout Pro/Checkout API y Marketplace; la Redirect URL debe registrarse en el panel.
- [Marketplace con Checkout Pro](https://www.mercadopago.com.mx/developers/es/docs/checkout-pro/how-tos/integrate-marketplace): usar el access token OAuth del seller y `marketplace_fee` al crear cada preferencia.
- [OAuth](https://www.mercadopago.com.mx/developers/es/docs/security/oauth): Authorization Code es el flujo aplicable; PKCE es opcional hasta que se habilita en la aplicación, momento en que pasa a ser obligatorio.

## Resultado técnico observado

- Producción usa token OAuth del profesional al crear preferencias de trabajos; no usa el token de plataforma para ese flujo.
- Para una base de 1,000.00 MXN el backend calcula `customer_total=1150.00`, `marketplace_fee=300.00` y `professional_amount_before_external_costs=850.00`.
- El backend consulta el pago en Mercado Pago y compara referencia, importe, moneda, modo y collector antes de conciliar.
- La comisión de Mercado Pago se debe tratar como `provider_fee`, separada de la comisión Chambapp. Los 850.00 son antes de costos externos, impuestos y retenciones; no son neto garantizado.

## Verificación manual obligatoria en el panel

1. Abrir Tus integraciones > aplicación Chambapp y confirmar producto Pagos online, Checkout Pro y modelo Marketplace/Split 1:1.
2. Confirmar Redirect URL exacta: `https://chambapp.com.mx/profesional/pagos/configuracion/oauth/callback`.
3. Confirmar webhook productivo exacto: `https://chambapp.com.mx/webhooks/mercadopago`, secreto correspondiente y eventos de pagos.
4. Confirmar si PKCE está habilitado. Si lo está, no habilitar producción hasta desplegar soporte PKCE.
5. Confirmar KYC 6 de cada seller, elegibilidad del marketplace y liberación de comisión con el ejecutivo de Mercado Pago cuando aplique.
6. Confirmar en cuentas de prueba los medios de pago realmente presentados por Checkout Pro. No se certifican de antemano tarjeta, saldo, transferencia, OXXO ni SPEI: su disponibilidad depende de cuenta, KYC, riesgo, país y reglas vigentes de Mercado Pago.

## Dictamen Split

**NO LISTO** para una prueba E2E: falta la confirmación del panel, PKCE no está implementado, falta retry/backoff y existen bloqueadores de despliegue documentados en la certificación.
