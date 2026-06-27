# Integración JARVIS — Zonix Glasses

Hub documentación negocio: **este repo** (`zonix-glasses-back/docs/`).  
Front espejo: `../zonix-glasses-front/docs/` (punteros, no duplicar canon).

## Repos

| Repo | Stack | Skills dominio |
|------|-------|----------------|
| `zonix-glasses-back` | Laravel 10 | `zonix-glasses-api-patterns`, `zonix-glasses-prescriptions`, … |
| `zonix-glasses-front` | Flutter | `zonix-glasses-ui-patterns`, `zonix-glasses-virtual-tryon` |

## Paso C (activo)

```bash
export JARVIS_SKILLS_LIBRARY=/var/www/html/proyectos/AIPP/jarvis-skills-library
./scripts/sync-global-skills-from-library.sh
./scripts/check-global-skills-sync.sh
python3 .agents/skills/sync.sh
```

Ambos repos: `./scripts/sync-all-zonix-glasses-skills.sh` (desde `zonix-glasses-back/scripts/`).

Verificación: `bash $JARVIS_SKILLS_LIBRARY/scripts/check-project-bootstrap.sh --min c`

## Spec Kit (híbrido)

1. Docs negocio en `docs/` (completar antes de implement).
2. Hub: `.specify/` + `specs/001-*` cuando founder apruebe docs.
3. **Implement** solo con OK explícito.

## Orden lectura IA

1. `.cursorrules` → `AGENTS.md` → `docs/active_context.md`
2. `docs/PRODUCT_VISION.md` → `docs/MODELO_NEGOCIO/`
3. Skills dominio `zonix-glasses-*`

## Git remoto

Reemplazar remote legacy Eats cuando exista repo Glasses en GitHub (ver `docs/CLONE_CHECKLIST.md`).

**Última actualización:** Junio 2026
