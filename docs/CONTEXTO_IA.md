# Contexto para la IA — Cómo mantenerse sincronizado

Este doc explica cómo tener **siempre** el mismo contexto en las herramientas de IA (Cursor, Angravity, Copilot con VS Code) sin depender de que el usuario pida "lee .cursorrules / README".

## Orden de lectura recomendado

1. **.cursorrules** — Reglas de colaboración, stack, roles, recordatorios.
2. **AGENTS.md** — Overview, cambios recientes, skills, índice a documentación detallada.
3. **docs/active_context.md** — Estado de la última sesión (resumen, áreas tocadas, próximos pasos).

La IA debe considerar estos tres como parte del estado actual del proyecto al iniciar o retomar.

**Fase actual (Jun 2026):** modelo de negocio **v3.1** documentado y coherente doc-vs-doc; **no programar** nuevas features hasta HITL legal/marca + [AUDIT_RIESGOS_SEGURIDAD.md](AUDIT_RIESGOS_SEGURIDAD.md). Detalle operativo: [active_context.md](active_context.md).

## Mapa `docs/` (fuente única por tema)

| Tema | Canon | Gates / memoria | Satélites |
|------|-------|-----------------|-----------|
| Decisiones founder | [MODELO_NEGOCIO/DECISIONES_FOUNDER.md](MODELO_NEGOCIO/DECISIONES_FOUNDER.md) | [CHECKLIST_FOUNDER_S11.md](MODELO_NEGOCIO/CHECKLIST_FOUNDER_S11.md) | — |
| Pagos / checkout / multa | [POLITICA_COMERCIAL.md](MODELO_NEGOCIO/POLITICA_COMERCIAL.md) | [HITL_APROBACION_DOCS.md](HITL_APROBACION_DOCS.md) | [FLUJOS_OPERATIVOS.md](MODELO_NEGOCIO/FLUJOS_OPERATIVOS.md) |
| Canal aliado | [POLITICA_CANAL_ALIADO.md](MODELO_NEGOCIO/POLITICA_CANAL_ALIADO.md) | HITL | [UNIT_ECONOMICS.md](MODELO_NEGOCIO/UNIT_ECONOMICS.md) |
| Entidades / agregados | [DOMINIO_DATOS.md](DOMINIO_DATOS.md) | — | — |
| Logística §11 | DECISIONES §11 | CHECKLIST §11 | [CADENA_SUMINISTRO.md](MODELO_NEGOCIO/CADENA_SUMINISTRO.md) |
| Visión producto | [PRODUCT_VISION.md](PRODUCT_VISION.md) | — | [LEAN_CANVAS.md](MODELO_NEGOCIO/LEAN_CANVAS.md) |
| Verificación doc | [VERIFICACION_MODELO_MULTI_PROVEEDOR.md](VERIFICACION_MODELO_MULTI_PROVEEDOR.md) | HITL | [AUDIT_FORENSE_2026-06-27.md](AUDIT_FORENSE_2026-06-27.md) |
| Ideas / backlog | [MEJORAS_MODELO_NEGOCIO.md](MEJORAS_MODELO_NEGOCIO.md) | — | — |
| Marca / privacidad | [BRAND_ZONIX_GLASSES.md](BRAND_ZONIX_GLASSES.md) · [PRIVACIDAD_OPTICA.md](PRIVACIDAD_OPTICA.md) | HITL (abierto) | — |
| Scaffold / clone | [SCAFFOLD_INVENTORY.md](SCAFFOLD_INVENTORY.md) · [CLONE_CHECKLIST.md](CLONE_CHECKLIST.md) | — | [ENV_VARIABLES.md](ENV_VARIABLES.md) |

## Dónde lee cada herramienta

| Herramienta   | Dónde suele leer contexto                          |
| ------------- | -------------------------------------------------- |
| **Cursor**    | `.cursorrules`, `AGENTS.md` (raíz); opcional `.cursor/rules/` |
| **Angravity** | Depende de la configuración del workspace; usar la misma raíz del repo (AGENTS.md, .cursorrules). |
| **Copilot (VS Code)** | Suele usar el archivo abierto y el repo; para reglas globales, `.github/copilot/` o documentación en raíz. |

## Sincronización

- **Fuente de verdad:** Este repo. `AGENTS.md`, `.cursorrules` y `docs/active_context.md` están en la raíz o en `docs/`.
- Para que Cursor, Angravity y Copilot vean lo mismo:
  - Abrir el **mismo directorio del repo** en cada herramienta (recomendado).
  - Si usás varias carpetas (ej. back y front por separado), cada una tiene su propio `AGENTS.md` y `docs/active_context.md`; el script `scripts/sync-context-for-ia.sh` puede servir para refrescar fechas o comprobar que los archivos existan.
- **Skills globales JARVIS:** instaladas en `~/.cursor/skills/` vía `jarvis-skills-library/scripts/install.sh`. Cursor las auto-descubre; **no copiar** al repo del producto.
- **Skills de dominio:** `.agents/skills/zonix-glasses-*/`. Cursor las referencia desde `AGENTS.md`.
- Para Angravity/Copilot: globales desde IDE; dominio desde `.agents/skills/`.

## Actualización del contexto

- **Al cerrar una sesión con cambios relevantes:** usar la skill **context-updater** para actualizar `docs/active_context.md`.
- **Al terminar una tarea:** usar la skill **documentar-avances** para proponer el párrafo de "Cambios recientes" (el usuario aprueba antes de aplicar).

Así la siguiente sesión (en cualquier herramienta) tiene contexto reciente sin pasos manuales.
