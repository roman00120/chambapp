# Política de Cookies

> **BORRADOR PARA REVISIÓN LEGAL.**

## Alcance observado

La web de Chambapp utiliza cookies técnicas necesarias para:

- mantener la sesión autenticada;
- proteger formularios y peticiones contra falsificación (XSRF/CSRF);
- recordar de manera limitada el estado necesario de navegación.

Se observaron cookies de sesión de Laravel y XSRF. No se encontró en el código auditado una plataforma publicitaria, píxel, Google Analytics u otra cookie analítica no esencial. Debe confirmarse en producción y en el CDN antes de publicar.

## Base y control

Las cookies estrictamente necesarias no pueden desactivarse desde Chambapp sin impedir funciones esenciales; la persona puede eliminarlas desde el navegador, lo que puede cerrar su sesión. Si se incorporan cookies de analítica, publicidad o terceros no necesarias, se deberá:

1. inventariarlas (nombre, proveedor, finalidad y duración);
2. solicitar elección previa y granular;
3. no premarcar consentimiento;
4. permitir retirar la elección tan fácilmente como otorgarla;
5. evitar cargarlas antes de consentir.

## Inventario pendiente

| Cookie | Proveedor | Finalidad | Duración |
|---|---|---|---|
| `XSRF-TOKEN` | Chambapp/Laravel | Seguridad de solicitudes | `[CONFIRMAR_CONFIGURACIÓN]` |
| cookie de sesión configurada | Chambapp/Laravel | Autenticación y estado | `[CONFIRMAR_CONFIGURACIÓN]` |
| cookies de CDN/Hostinger | `[CONFIRMAR]` | Seguridad/rendimiento | `[CONFIRMAR]` |

Contacto: `[CORREO_ARCO_PENDIENTE]`.

