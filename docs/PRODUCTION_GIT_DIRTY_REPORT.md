# Production Git dirty report

Fecha: 2026-08-26. Inspección SSH de sólo lectura; no se alteró código productivo.

| Clasificación | Hallazgo | Acción segura propuesta |
|---|---|---|
| E — código no versionado | 53 archivos versionados modificados en `app`, `config`, `resources`, `routes` y `bootstrap`; además 14 archivos nuevos de identidad y dos migraciones | Preservar como patch/commit revisado. No resetear ni desplegar hasta comparar contra un commit aprobado. |
| B — runtime incorrectamente versionado | Cientos de `storage/framework/views/*.php` eliminados; `storage/logs/laravel.log` y fixtures de testing aparecen versionados | Corregir tracking en un commit; el runtime no debe formar parte de releases. No usar `git clean` sobre storage/uploads. |
| D — log | `error_log` sin seguimiento | Excluirlo y conservarlo fuera del release/retención operativa. |
| F/C — backup/config secreto | `.env.backup.mercadopago.20260825T051752Z`, no trackeado ni ignorado en producción, modo 0600 | Mover de forma autorizada a `~/deploy-backups/secrets`, modo 0700/0600; no borrar. |
| A — assets | `public/images/chambapp-logo.png` sin seguimiento | Revisar origen/licencia y versionar sólo si es parte del release. |

El backup está fuera de `public_html` y las rutas `/.env`, `/.git/config` y la ruta del backup retornaron 403 por HTTPS. Eso no sustituye retirarlo del árbol servido. La producción no es reproducible: HEAD es `24e8ef6` pero el worktree no corresponde exclusivamente a ese commit.

## Requisito de release reproducible

Construir desde un commit limpio en CI/artefacto inmutable, mantener `.env` y secretos fuera del repositorio/release, y enlazar sólo directorios runtime explícitos (`storage` persistente/uploads) con reglas de exclusión. Antes de cualquier despliegue, revisar `git diff --name-status` y asociar el artefacto al SHA exacto.
