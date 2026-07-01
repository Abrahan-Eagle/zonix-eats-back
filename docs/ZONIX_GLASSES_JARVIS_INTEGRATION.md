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

1. Docs negocio en `docs/` — **v3.1 cerrado doc-vs-doc** (Jun 2026); pendiente HITL legal/marca/seguridad antes de implement.
2. Hub: `.specify/` + `specs/001-*` — código **congelado** hasta post-HITL.
3. **Implement** solo con OK explícito founder (tras legal + AUDIT_RIESGOS).

## Orden lectura IA

Ver mapa completo en [CONTEXTO_IA.md](CONTEXTO_IA.md). Resumen: `.cursorrules` → `AGENTS.md` → `active_context.md` → canon `MODELO_NEGOCIO/` → skills `zonix-glasses-*`.

## Git remoto

Reemplazar remote legacy Eats cuando exista repo Glasses en GitHub (ver `docs/CLONE_CHECKLIST.md`).

**Última actualización:** Junio 2026
